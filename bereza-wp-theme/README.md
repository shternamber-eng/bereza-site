# БЕРЕЗА — WordPress тема

Кастомна WordPress тема для особистого медіа-проєкту **БЕРЕЗА**. Газетний стиль, темна палітра, підтримка Custom Post Types, ACF та Gutenberg-блоків.

---

## Вимоги

| Компонент | Версія |
|---|---|
| WordPress | 6.0+ |
| PHP | 8.0+ |
| [Advanced Custom Fields PRO](https://www.advancedcustomfields.com/pro/) | 6.0+ |

> Без ACF PRO тема працює, але поля метаданих (час читання, лід, соцмережі тощо) і Gutenberg-блоки недоступні.

---

## Встановлення

### Варіант А — через WordPress Admin (рекомендовано)

1. Завантажте файл **`bereza-wp-theme.zip`** з розділу [Releases](../../releases).
2. У WordPress: **Зовнішній вигляд → Теми → Додати нову → Завантажити тему**.
3. Виберіть ZIP-файл → **Встановити** → **Активувати**.

### Варіант Б — вручну через FTP / File Manager

1. Розпакуйте `bereza-wp-theme.zip`.
2. Скопіюйте папку `bereza-wp-theme/` до `/wp-content/themes/`.
3. Активуйте тему у **Зовнішній вигляд → Теми**.

---

## Після активації

### 1. Оновіть структуру URL

**Налаштування → Постійні посилання → Зберегти зміни**

Це потрібно, щоб WordPress зареєстрував нові Custom Post Types та їх архіви:

| URL | Розділ |
|---|---|
| `/kolumny/` | Авторські колонки |
| `/rozsliduvannya/` | Розслідування |
| `/video/` | Відео |
| `/podkast/` | Подкаст |

### 2. Заповніть налаштування сайту

Перейдіть до **БЕРЕЗА** у бічному меню адмін-панелі:

| Поле | Опис |
|---|---|
| № випуску | Відображається у шапці (наприклад: `184`) |
| Місто | Наприклад: `Київ` |
| Температура | Наприклад: `9°C` |
| Тираж | Наприклад: `184 700` |
| Рядок тікера | Рядки, що прокручуються у верхній панелі |
| Цитата | Текст і підпис цитати на головній |
| Соціальні мережі | Іконка, назва, кількість підписників, URL |
| Email (основний) | Наприклад: `hello@bereza.media` |
| Email (для тіпів) | Наприклад: `tip@bereza.media` |
| MailerLite API Key | Ключ API для підписки на розсилку |
| MailerLite Group ID | ID групи в MailerLite |

### 3. Налаштуйте підписку (опціонально)

**Без MailerLite:** Email-адреси зберігаються у базі даних WordPress (`wp-options`, ключ `bereza_subscribers`).

**З MailerLite:** Вкажіть API Key і Group ID у налаштуваннях теми. Форма автоматично передаватиме підписників через MailerLite API v2.

Альтернативно — задайте константи у `wp-config.php`:

```php
define('BEREZA_MAILERLITE_KEY',   'ваш-api-key');
define('BEREZA_MAILERLITE_GROUP', 'ваш-group-id');
```

---

## Custom Post Types

| Slug | Архів | Призначення |
|---|---|---|
| `kolumna` | `/kolumny/` | Авторські колонки |
| `rozsliduvannya` | `/rozsliduvannya/` | Розслідування |
| `video` | `/video/` | Відео-матеріали |
| `podkast` | `/podkast/` | Епізоди подкасту |

## ACF Поля

### Для публікацій (post, kolumna, rozsliduvannya, podkast)

| Поле | Тип | Опис |
|---|---|---|
| `is_hero` | true/false | Позначити як головний матеріал на головній |
| `is_urgent` | true/false | Червоний тег «терміново» |
| `category_label` | text | Назва рубрики (тег над заголовком) |
| `lede` | textarea | Короткий анонс / підзаголовок |
| `read_time` | text | Час читання: `14 хв` |
| `sources` | repeater | Джерела: назва + посилання |

### Для відео (video)

| Поле | Тип | Опис |
|---|---|---|
| `youtube_url` | url | Посилання на YouTube |
| `duration` | text | Тривалість: `42:18` |
| `view_meta` | text | Текст переглядів: `147 тис. · 3 дні тому` |

## Gutenberg-блоки (ACF PRO)

| Блок | Опис |
|---|---|
| **БЕРЕЗА: Цитата** | Велика редакційна цитата з підписом |
| **БЕРЕЗА: Головна карточка** | Hero-карточка з тегом, заголовком і лідом |
| **БЕРЕЗА: Форма підписки** | Повна секція підписки з каналами |

---

## Структура файлів

```
bereza-wp-theme/
├── style.css                   # Заголовок теми
├── functions.php               # Підключення, AJAX-підписка, хелпери
├── index.php                   # Фолбек-шаблон
├── front-page.php              # Головна сторінка
├── header.php                  # Topbar + masthead + nav
├── footer.php                  # Футер
├── single.php                  # Сторінка статті
├── archive.php                 # Архів CPT
├── page.php                    # Звичайна сторінка
├── page-pro-avtora.php         # Шаблон «Про автора»
├── template-parts/
│   ├── hero.php                # Головний блок + боковина
│   ├── columns-section.php     # Авторські колонки
│   ├── video-section.php       # Відео-сітка
│   ├── quote-section.php       # Цитата
│   ├── latest-section.php      # Свіжі матеріали
│   └── subscribe-section.php   # Форма підписки + канали
├── inc/
│   ├── cpt.php                 # Реєстрація Custom Post Types
│   ├── acf-fields.php          # Реєстрація ACF полів
│   └── blocks.php              # Gutenberg-блоки
└── assets/
    ├── css/                    # reset / tokens / base / layout / components
    └── js/main.js              # Тікер, AJAX-підписка, пошук
```
