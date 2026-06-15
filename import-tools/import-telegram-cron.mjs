// Автоматический (cron) импорт новых постов из t.me/BerezaJuice на bereza.center
// как live-публикации через REST API.
//
// В отличие от import-bereza-articles.mjs, не берёт период по дате, а хранит
// id последнего обработанного поста в STATE_FILE и публикует только то, что
// новее. При первом запуске (файла состояния нет) ничего не публикует —
// только запоминает текущий "последний" пост (bootstrap), чтобы не залить
// сайт всем историческим архивом канала.
//
// Использование (локально, для теста бутстрапа):
//   $env:WP_USER = "..."
//   $env:WP_APP_PASSWORD = "..."
//   node import-telegram-cron.mjs
//
// В GitHub Actions переменные WP_USER/WP_APP_PASSWORD приходят из secrets,
// а STATE_FILE коммитится обратно ботом после запуска.

import { mkdir, readFile, writeFile } from "node:fs/promises";
import path from "node:path";
import { fileURLToPath } from "node:url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const WP_BASE_URL = (process.env.WP_BASE_URL || "https://bereza.center").replace(/\/+$/, "");
const TELEGRAM_CHANNEL = "BerezaJuice";
const MAX_PAGES = 10;
const STATE_FILE = path.join(__dirname, "state", "telegram-last-id.json");

const UA =
  "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36";

// Украинская офіційна транслітерація — щоб уникнути кириличних percent-encoded slug-ів.
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

async function fetchHtml(url) {
  const res = await fetch(url, { headers: { "User-Agent": UA } });
  return res.text();
}

function telegramTextToParagraphs(rawHtml) {
  return rawHtml
    .replace(/<br\s*\/?>/gi, "\n")
    .split(/\n+/)
    .map((line) =>
      line
        .replace(/<[^>]+>/g, "")
        .replace(/&nbsp;/g, " ")
        .replace(/&amp;/g, "&")
        .replace(/&laquo;/g, "«")
        .replace(/&raquo;/g, "»")
        .replace(/&mdash;/g, "—")
        .replace(/&#39;/g, "'")
        .replace(/&quot;/g, '"')
        .trim()
    )
    .filter(Boolean)
    .map((text) => `<p>${text}</p>`)
    .join("\n");
}

// У постів у Telegram немає заголовків — беремо перший рядок тексту як заголовок публікації.
function deriveTelegramTitle(plainText) {
  const firstLine = plainText
    .split("\n")
    .map((s) => s.trim())
    .filter(Boolean)[0];
  if (!firstLine) return "Допис у Telegram";
  return firstLine.length > 100 ? `${firstLine.slice(0, 97)}…` : firstLine;
}

async function loadState() {
  try {
    const raw = await readFile(STATE_FILE, "utf8");
    return JSON.parse(raw);
  } catch {
    return null;
  }
}

async function saveState(state) {
  await mkdir(path.dirname(STATE_FILE), { recursive: true });
  await writeFile(STATE_FILE, `${JSON.stringify(state, null, 2)}\n`, "utf8");
}

// Повертає найбільший msgId на першій сторінці стрічки каналу (для бутстрапу).
async function getLatestMsgId() {
  const html = await fetchHtml(`https://t.me/s/${TELEGRAM_CHANNEL}`);
  let maxId = null;
  for (const block of html.split('data-post="').slice(1)) {
    const postMatch = block.match(/^([\w.]+)\/(\d+)"/);
    if (!postMatch) continue;
    const msgId = Number(postMatch[2]);
    if (maxId === null || msgId > maxId) maxId = msgId;
  }
  return maxId;
}

// Зчитує стрічку каналу, повертає пости з msgId > afterId (від найстаріших до
// найновіших) та максимальний побачений msgId.
async function collectNewTelegramPosts(afterId) {
  const found = [];
  let maxId = afterId;
  let beforeId = null;

  for (let page = 1; page <= MAX_PAGES; page++) {
    const listUrl = beforeId
      ? `https://t.me/s/${TELEGRAM_CHANNEL}?before=${beforeId}`
      : `https://t.me/s/${TELEGRAM_CHANNEL}`;

    let html;
    try {
      html = await fetchHtml(listUrl);
    } catch (err) {
      console.warn(`[telegram] стр. ${page}: ${err.message}`);
      break;
    }

    const blocks = html.split('data-post="').slice(1);
    if (blocks.length === 0) break;

    let minId = null;
    let stop = false;

    for (const block of blocks) {
      const postMatch = block.match(/^([\w.]+)\/(\d+)"/);
      if (!postMatch) continue;
      const [, channel, msgIdStr] = postMatch;
      const msgId = Number(msgIdStr);
      if (minId === null || msgId < minId) minId = msgId;
      if (msgId > maxId) maxId = msgId;

      if (msgId <= afterId) {
        stop = true;
        continue;
      }

      const textMatch = block.match(/<div class="tgme_widget_message_text[^"]*"[^>]*>([\s\S]*?)<\/div>/);
      if (!textMatch) continue; // пост без тексту (фото/відео без підпису)

      const content = telegramTextToParagraphs(textMatch[1]);
      const plain = content.replace(/<[^>]+>/g, "\n");
      if (!plain.trim()) continue;

      const dateMatch = block.match(/<time datetime="([^"]+)"/);

      found.push({
        msgId,
        url: `https://t.me/${channel}/${msgIdStr}`,
        title: deriveTelegramTitle(plain),
        date: dateMatch ? new Date(dateMatch[1]) : new Date(),
        content,
      });
    }

    if (stop || minId === null) break;
    beforeId = minId;
  }

  found.sort((a, b) => a.msgId - b.msgId); // від старих до нових
  return { posts: found, maxId };
}

function authHeader() {
  const user = process.env.WP_USER;
  const appPassword = process.env.WP_APP_PASSWORD;
  if (!user || !appPassword) {
    throw new Error("Задайте WP_USER и WP_APP_PASSWORD в переменных окружения перед запуском.");
  }
  return "Basic " + Buffer.from(`${user}:${appPassword}`).toString("base64");
}

async function publishPost(post) {
  const body = {
    title: post.title,
    content: `${post.content}\n<p><em>Першоджерело: <a href="${post.url}" rel="nofollow noopener" target="_blank">${post.url}</a></em></p>`,
    slug: slugify(post.title) || `post-${post.msgId}`,
    status: "publish",
    date: post.date.toISOString().replace(/\.\d{3}Z$/, ""),
    comment_status: "closed",
  };

  const res = await fetch(`${WP_BASE_URL}/wp-json/wp/v2/posts`, {
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
  const state = await loadState();

  if (!state) {
    console.log("Файл состояния не найден — бутстрап без публикации.");
    const maxId = await getLatestMsgId();
    if (maxId === null) {
      throw new Error("Не удалось получить ленту канала для бутстрапа.");
    }
    await saveState({ lastId: maxId });
    console.log(`Сохранён lastId = ${maxId}. С следующего запуска будут публиковаться посты новее этого.`);
    return;
  }

  console.log(`Известный lastId = ${state.lastId}. Ищу новые посты...`);
  const { posts, maxId } = await collectNewTelegramPosts(state.lastId);

  if (posts.length === 0) {
    console.log("Новых постов нет.");
  } else {
    console.log(`Найдено новых постов: ${posts.length}`);
    for (const post of posts) {
      try {
        const id = await publishPost(post);
        console.log(`  OK  post #${id}  ${post.title}`);
      } catch (err) {
        console.error(`  FAIL  ${post.title}: ${err.message}`);
      }
    }
  }

  if (maxId !== state.lastId) {
    await saveState({ lastId: maxId });
    console.log(`Обновлён lastId = ${maxId}.`);
  }
}

main().catch((err) => {
  console.error("Ошибка:", err.message);
  process.exitCode = 1;
});
