// Автоматичний імпорт нових відео з YouTube-каналу в CPT "video" на bereza.center.
//
// Читає той самий публічний RSS-фід, що й головна сторінка (bereza_get_youtube_videos()
// у functions.php), і публікує кожне нове відео як WP-запис типу video, якщо воно ще
// не було імпортоване раніше.
//
// ВАЖЛИВО: RSS-фід YouTube віддає лише ~15 останніх відео каналу (без пагінації).
// Тому цей скрипт не відновить весь історичний архів за минулі місяці — лише
// підхопить те, що зараз є в стрічці, і надалі не пропускатиме нові відео.
//
// Стан (список вже імпортованих YouTube video ID) зберігається в STATE_FILE.
//
// Використання (локально):
//   $env:WP_USER = "..."
//   $env:WP_APP_PASSWORD = "..."
//   node import-tools/import-youtube-videos-cron.mjs
//
// В GitHub Actions WP_USER/WP_APP_PASSWORD приходять із secrets, а STATE_FILE
// коммітиться назад ботом після запуску (як і для телеграм-імпорту).

import { mkdir, readFile, writeFile } from "node:fs/promises";
import path from "node:path";
import { fileURLToPath } from "node:url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const WP_BASE_URL = (process.env.WP_BASE_URL || "https://bereza.center").replace(/\/+$/, "");
const CHANNEL_ID = process.env.YOUTUBE_CHANNEL_ID || "UCWTGZEIgCCjr0K07KJzae7g";
const STATE_FILE = path.join(__dirname, "state", "youtube-imported-ids.json");
const MAX_TRACKED_IDS = 300;

const UA =
  "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36";

// Українська офіційна транслітерація — щоб уникнути кириличних percent-encoded slug-ів.
const TRANSLIT = {
  а: "a", б: "b", в: "v", г: "h", ґ: "g", д: "d", е: "e", є: "ie",
  ж: "zh", з: "z", и: "y", і: "i", ї: "i", й: "i", к: "k", л: "l",
  м: "m", н: "n", о: "o", п: "p", р: "r", с: "s", т: "t", у: "u",
  ф: "f", х: "kh", ц: "ts", ч: "ch", ш: "sh", щ: "shch", ю: "iu", я: "ia",
  ь: "", "'": "", "’": "",
  ы: "y", э: "e", ё: "e", ъ: "",
};

function slugify(text) {
  return text
    .toLowerCase()
    .split("")
    .map((ch) => (ch in TRANSLIT ? TRANSLIT[ch] : ch))
    .join("")
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "")
    .replace(/-{2,}/g, "-");
}

function decodeEntities(text) {
  return text
    .replace(/&amp;/g, "&")
    .replace(/&quot;/g, '"')
    .replace(/&#39;/g, "'")
    .replace(/&lt;/g, "<")
    .replace(/&gt;/g, ">")
    .trim();
}

async function loadImportedIds() {
  try {
    const raw = await readFile(STATE_FILE, "utf8");
    const parsed = JSON.parse(raw);
    return new Set(parsed.importedIds || []);
  } catch {
    return new Set();
  }
}

async function saveImportedIds(importedIds) {
  await mkdir(path.dirname(STATE_FILE), { recursive: true });
  const trimmed = importedIds.slice(-MAX_TRACKED_IDS);
  await writeFile(STATE_FILE, `${JSON.stringify({ importedIds: trimmed }, null, 2)}\n`, "utf8");
}

async function fetchChannelVideos() {
  const res = await fetch(
    `https://www.youtube.com/feeds/videos.xml?channel_id=${encodeURIComponent(CHANNEL_ID)}`,
    { headers: { "User-Agent": UA } }
  );
  if (!res.ok) throw new Error(`HTTP ${res.status} при читанні YouTube RSS`);
  const xml = await res.text();

  const videos = [];
  for (const block of xml.split("<entry>").slice(1)) {
    const idMatch = block.match(/<yt:videoId>([^<]+)<\/yt:videoId>/);
    const titleMatch = block.match(/<title>([\s\S]*?)<\/title>/);
    const publishedMatch = block.match(/<published>([^<]+)<\/published>/);
    const thumbMatch = block.match(/<media:thumbnail url="([^"]+)"/);
    if (!idMatch || !titleMatch || !publishedMatch) continue;

    videos.push({
      id: idMatch[1],
      title: decodeEntities(titleMatch[1]),
      published: new Date(publishedMatch[1]),
      thumbnail: thumbMatch ? thumbMatch[1] : null,
    });
  }

  videos.sort((a, b) => a.published - b.published); // від старих до нових
  return videos;
}

function authHeader() {
  const user = process.env.WP_USER;
  const appPassword = process.env.WP_APP_PASSWORD;
  if (!user || !appPassword) {
    throw new Error("Задайте WP_USER и WP_APP_PASSWORD в переменных окружения перед запуском.");
  }
  return "Basic " + Buffer.from(`${user}:${appPassword}`).toString("base64");
}

async function uploadThumbnail(url, videoId) {
  const res = await fetch(url, { headers: { "User-Agent": UA } });
  if (!res.ok) throw new Error(`HTTP ${res.status} при скачуванні превью`);
  const buffer = Buffer.from(await res.arrayBuffer());
  const contentType = res.headers.get("content-type") || "image/jpeg";

  const uploadRes = await fetch(`${WP_BASE_URL}/wp-json/wp/v2/media`, {
    method: "POST",
    headers: {
      "Content-Type": contentType,
      "Content-Disposition": `attachment; filename="youtube-${videoId}.jpg"`,
      Authorization: authHeader(),
    },
    body: buffer,
  });

  if (!uploadRes.ok) {
    const text = await uploadRes.text();
    throw new Error(`HTTP ${uploadRes.status}: ${text.slice(0, 200)}`);
  }
  const json = await uploadRes.json();
  return json.id;
}

async function publishVideo(video) {
  let featuredMediaId = null;
  if (video.thumbnail) {
    try {
      featuredMediaId = await uploadThumbnail(video.thumbnail, video.id);
    } catch (err) {
      console.warn(`    [thumb] не вдалося завантажити превью: ${err.message}`);
    }
  }

  const content =
    `<figure class="wp-block-embed is-type-video wp-block-embed-youtube">\n` +
    `<div class="wp-block-embed__wrapper"><iframe loading="lazy" src="https://www.youtube.com/embed/${video.id}" allowfullscreen></iframe></div>\n` +
    `</figure>`;

  const body = {
    title: video.title,
    content,
    slug: slugify(video.title) || `video-${video.id}`,
    status: "publish",
    date: video.published.toISOString().replace(/\.\d{3}Z$/, ""),
    comment_status: "closed",
    ...(featuredMediaId ? { featured_media: featuredMediaId } : {}),
  };

  const res = await fetch(`${WP_BASE_URL}/wp-json/wp/v2/video`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Authorization: authHeader(),
    },
    body: JSON.stringify(body),
  });

  if (!res.ok) {
    const text = await res.text();
    throw new Error(`HTTP ${res.status}: ${text.slice(0, 300)}`);
  }
  const json = await res.json();
  return json.id;
}

async function main() {
  const importedIds = await loadImportedIds();
  console.log(`Відомо імпортованих відео: ${importedIds.size}`);

  const videos = await fetchChannelVideos();
  const newVideos = videos.filter((v) => !importedIds.has(v.id));

  if (newVideos.length === 0) {
    console.log("Нових відео немає.");
    return;
  }

  console.log(`Знайдено нових відео: ${newVideos.length}`);
  const updatedIds = [...importedIds];

  for (const video of newVideos) {
    try {
      const id = await publishVideo(video);
      console.log(`  OK    video #${id}  ${video.title}`);
      updatedIds.push(video.id);
    } catch (err) {
      console.error(`  FAIL  ${video.title}: ${err.message}`);
      // не додаємо в importedIds — спробуємо опублікувати ще раз наступного разу
    }
  }

  await saveImportedIds(updatedIds);
}

main().catch((err) => {
  console.error("Помилка:", err.message);
  process.exitCode = 1;
});
