<?php

require_once __DIR__ . '/../models/ImageConverter.php';

/**
 * ImageController
 *
 * Acts as the single HTTP entry-point for the image converter feature.
 * Follows the Front-Controller / MVC pattern:
 *
 *   - Reads and validates request data ($_FILES, $_POST, $_GET, $_COOKIE).
 *   - Delegates all image-processing work to the ImageConverter model.
 *   - Resolves the active UI language and loads the matching translation table.
 *   - Passes the resulting data to the view layer for rendering.
 *
 * This class intentionally contains no HTML and no GD calls; it is purely
 * responsible for orchestration and data flow between the model and the view.
 */
class ImageController
{
    /** @var array<string, string> Active translation table loaded from lang/. */
    private array $t;

    /** @var string Active language code, e.g. 'en', 'pt_BR', 'es'. */
    private string $lang;

    /**
     * Conversion result payload returned by ImageConverter::convert().
     * Null when no conversion has been performed in the current request.
     *
     * @var array{name:string, mime:string, size:int, b64:string, w:int, h:int}|null
     */
    private ?array $result = null;

    /**
     * Translated error message to display in the view.
     * Null when the last conversion (if any) succeeded.
     *
     * @var string|null
     */
    private ?string $error = null;

    /**
     * Ready-to-use data-URI string for the <img> preview in the view.
     * Format: "data:{mime};base64,{b64}".
     * Null when no successful conversion has been performed.
     *
     * @var string|null
     */
    private ?string $preview = null;

    /** Language codes that have a corresponding file in lang/. */
    private const SUPPORTED_LANGS = ['en', 'pt_BR', 'es'];

    /** Fallback language used when the request carries no recognised code. */
    private const DEFAULT_LANG = 'pt_BR';

    /**
     * Bootstrap the controller.
     *
     * Resolves the active language (from GET, POST, cookie, or default),
     * persists it in a 30-day cookie, and loads the matching translation array
     * so that every subsequent method has access to localised strings via $this->t.
     */
    public function __construct()
    {
        $this->lang = $this->resolveLang();
        $this->t    = require __DIR__ . "/../lang/{$this->lang}.php";
    }

    /**
     * Dispatch the current HTTP request.
     *
     * The only public method — acts as the single entry-point called by
     * index.php. Routes POST requests through the upload/conversion pipeline
     * and always ends by rendering the view, regardless of success or failure.
     *
     * @return void
     */
    public function handle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processUpload();
        }

        $this->renderView();
    }

    // ── Private ───────────────────────────────────────────────────────────

    /**
     * Process an incoming file upload and trigger the conversion.
     *
     * Reads 'image' from $_FILES and 'format'/'quality' from $_POST, then
     * delegates to ImageConverter::convert(). On success, populates
     * $this->result and $this->preview. On failure, catches the RuntimeException
     * thrown by the model, resolves its message as a translation key, and
     * stores the localised error string in $this->error for the view to render.
     *
     * Silently returns early if no file was uploaded or the upload failed
     * at the PHP/HTTP level (e.g. file too large, partial upload).
     *
     * @return void
     */
    private function processUpload(): void
    {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            return;
        }

        $format    = $_POST['format']  ?? 'png';
        $quality   = (int) ($_POST['quality'] ?? 90);
        $converter = new ImageConverter();

        try {
            $this->result  = $converter->convert($_FILES['image'], $format, $quality);
            $this->preview = "data:{$this->result['mime']};base64,{$this->result['b64']}";
        } catch (RuntimeException $e) {
            // The model throws RuntimeException with a translation key as the message.
            $key         = $e->getMessage();
            $this->error = $this->t[$key] ?? $key;
        }
    }

    /**
     * Resolve the language code to use for the current request.
     *
     * Lookup priority (first match wins):
     *   1. $_GET['lang']    – explicit URL parameter, highest priority.
     *   2. $_POST['lang']   – hidden field included in the conversion form.
     *   3. $_COOKIE['lang'] – persisted from a previous visit.
     *   4. DEFAULT_LANG     – hardcoded fallback ('pt_BR').
     *
     * If the resolved code is in SUPPORTED_LANGS the cookie is refreshed for
     * another 30 days; otherwise DEFAULT_LANG is returned without setting a cookie.
     *
     * @return string A valid language code present in SUPPORTED_LANGS.
     */
    private function resolveLang(): string
    {
        $requested = $_GET['lang'] ?? $_POST['lang'] ?? $_COOKIE['lang'] ?? self::DEFAULT_LANG;

        if (in_array($requested, self::SUPPORTED_LANGS, true)) {
            setcookie('lang', $requested, time() + 60 * 60 * 24 * 30, '/');
            return $requested;
        }

        return self::DEFAULT_LANG;
    }

    /**
     * Render the converter view by including the PHP template.
     *
     * Extracts controller state into local variables before the require so
     * that the view receives a clean, minimal set of named variables:
     *
     *   $t       – translation array for the active language.
     *   $lang    – active language code (used for the lang switcher UI).
     *   $result  – conversion result array, or null if none.
     *   $error   – localised error string, or null if none.
     *   $preview – data-URI string for the preview <img>, or null if none.
     *
     * The view has read-only access to these variables; it must not modify them.
     *
     * @return void
     */
    private function renderView(): void
    {
        $t       = $this->t;
        $lang    = $this->lang;
        $result  = $this->result;
        $error   = $this->error;
        $preview = $this->preview;

        require __DIR__ . '/../views/converter.php';
    }
}