# PHP Image Converter

A clean, server-side image format converter built with PHP and the GD extension. Converts between JPEG, PNG, WebP, GIF, and BMP with adjustable quality, drag-and-drop file input, live preview, and a fully localised UI in three languages.

---

## Features

- **Format conversion** — JPEG ↔ PNG ↔ WebP ↔ GIF ↔ BMP (AVIF accepted as input when the PHP build supports it)
- **Quality control** — 1–100 slider; PNG compression level is derived automatically
- **Transparency preservation** — PNG, WebP, and GIF outputs keep the alpha channel; JPEG and BMP flatten to white
- **Drag-and-drop upload** with instant client-side thumbnail preview
- **Inline result preview** with metadata (format, dimensions, file size)
- **One-click download** via data-URI — no temporary files written to disk
- **Internationalisation** — English (en), Brazilian Portuguese (pt_BR), Spanish (es)
- **Language persistence** — choice stored in a 30-day cookie, switchable at any time via the top-right nav
- **MVC architecture** — model, view, and controller are fully separated; the view contains zero business logic

---

## Requirements

| Requirement | Minimum version |
|---|---|
| PHP | 8.1 |
| GD extension | bundled with PHP (must be enabled) |
| Web server | Apache, Nginx, or PHP built-in server |

> **Tip:** verify GD is active by running `php -m | grep -i gd`. You should see `gd` in the output. If not, uncomment `extension=gd` in your `php.ini` and restart the server.

---

## Installation

### 1 — Clone or download

```bash
git clone git@github.com:fernandes5000/claude-image-converter.git
cd claude-image-converter
```

Or simply unzip the downloaded archive into your web root.

### 2 — Directory structure

Ensure the files are laid out exactly as follows before starting the server:

```
project-root/
├── index.php
├── README.md
├── controllers/
│   └── ImageController.php
├── models/
│   └── ImageConverter.php
├── views/
│   └── converter.php
└── lang/
    ├── en.php
    ├── pt_BR.php
    └── es.php
```

### 3 — Start a server

**PHP built-in server (development)**

```bash
php -S localhost:8080
```

Then open [http://localhost:8080](http://localhost:8080) in your browser.

**Apache**

Place the project folder inside your `DocumentRoot` (e.g. `/var/www/html/converter`) and navigate to `http://localhost:8080/converter`.

**Nginx**

Point the `root` directive at the project folder and ensure `.php` files are passed to `php-fpm`.

---

## Usage

1. Open the application in your browser.
2. Drag an image onto the drop zone, or click it to open the file picker.
3. Select the target format from the **Convert to** dropdown.
4. Adjust the **Quality** slider if needed (higher = better quality, larger file).
5. Click **Convert now**.
6. Inspect the preview and metadata, then click **Download** to save the converted file.

---

## Changing the language

Click **EN**, **PT**, or **ES** in the top-right corner at any time. The selection is remembered across visits via a cookie. You can also force a language through the URL:

```
http://localhost:8080/?lang=en
http://localhost:8080/?lang=pt_BR
http://localhost:8080/?lang=es
```

---

## Project architecture

This project follows the **Model–View–Controller** pattern.

```
Request
  │
  ▼
index.php  ──────────────────────────  Front controller
  │                                    Loads classes, instantiates
  │                                    ImageController, calls handle().
  ▼
ImageController::handle()  ──────────  Controller
  │                                    Resolves language, reads $_FILES
  │                                    and $_POST, delegates to model,
  │                                    passes data to view.
  ├──► ImageConverter::convert()  ───  Model
  │                                    Validates MIME, loads GD resource,
  │                                    composites canvas, encodes output,
  │                                    returns result array or throws.
  │
  └──► views/converter.php  ─────────  View
                                       Pure HTML/CSS/JS template. Receives
                                       $t, $lang, $result, $error, $preview.
                                       Contains zero business logic.
```

### Key files

| File | Responsibility |
|---|---|
| `index.php` | Front controller — single HTTP entry-point |
| `controllers/ImageController.php` | Request handling, language resolution, view rendering |
| `models/ImageConverter.php` | All GD image-processing logic |
| `views/converter.php` | HTML template — UI only |
| `lang/en.php` | English translation strings |
| `lang/pt_BR.php` | Brazilian Portuguese translation strings |
| `lang/es.php` | Spanish translation strings |

---

## Adding a new language

1. Copy `lang/en.php` to `lang/xx.php` (replace `xx` with the desired language code).
2. Translate every string value in the new file; **do not change the keys**.
3. Register the new code in `ImageController.php`:

```php
private const SUPPORTED_LANGS = ['en', 'pt_BR', 'es', 'xx'];
```

4. Add the button to the language switcher in `views/converter.php`:

```php
<?php foreach (['en' => 'EN', 'pt_BR' => 'PT', 'es' => 'ES', 'xx' => 'XX'] as $code => $label): ?>
```

No other changes are required.

---

## Supported formats

| Format | Input | Output | Transparency | Quality control |
|---|---|---|---|---|
| JPEG | ✅ | ✅ | ❌ (flattened to white) | ✅ |
| PNG | ✅ | ✅ | ✅ | ✅ (mapped to compression level) |
| WebP | ✅ | ✅ | ✅ | ✅ |
| GIF | ✅ | ✅ | ✅ | ❌ (lossless) |
| BMP | ✅ | ✅ | ❌ (flattened to white) | ❌ (lossless) |
| AVIF | ✅ *(if PHP supports it)* | ❌ | — | — |

---

## Error handling

All conversion errors are thrown by the model as `RuntimeException` with a translation key as the message (e.g. `'error_format'`). The controller catches the exception, resolves the key against the active language table, and passes the human-readable string to the view. This keeps error messages decoupled from the model and fully localisable.

| Exception message | Meaning |
|---|---|
| `error_format` | The uploaded file's MIME type is not in the supported input list |
| `error_read` | GD could not decode the source file (corrupted or unsupported variant) |
| `error_convert` | GD failed to encode the output in the requested format |

---

## Security considerations

- MIME type is detected server-side via `mime_content_type()` on the temp file — the client-supplied filename and `Content-Type` header are ignored for validation.
- No converted files are written to disk; the result is encoded to base64 in memory and delivered as a data-URI, eliminating temporary-file cleanup and path-traversal risks.
- All user-supplied strings rendered in HTML are passed through `htmlspecialchars()`.
- Consider adding `upload_max_filesize` and `post_max_size` limits in `php.ini` appropriate for your deployment.

---

## License

MIT — see `LICENSE` for details.