<?php
declare(strict_types=1);

/**
 * Application Entry Point — Front Controller
 *
 * This is the single HTTP entry-point for the image converter application.
 * Every request, regardless of method (GET or POST), is routed through here.
 *
 * Responsibilities:
 *   1. Load the Model (ImageConverter) and Controller (ImageController) classes.
 *   2. Instantiate the controller, which bootstraps language resolution.
 *   3. Delegate request handling to ImageController::handle(), which processes
 *      any uploaded file, populates result/error state, and renders the view.
 *
 * No business logic lives here — this file is intentionally kept as thin as
 * possible so that adding routes, middleware, or a DI container in the future
 * requires changes in only one place.
 *
 * Expected directory layout:
 *   index.php               ← this file
 *   controllers/
 *     ImageController.php
 *   models/
 *     ImageConverter.php
 *   views/
 *     converter.php
 *   lang/
 *     en.php
 *     pt_BR.php
 *     es.php
 */

// ── Load classes ──────────────────────────────────────────────────────────
// Model must be required before the controller because ImageController
// instantiates ImageConverter directly (no dependency injection yet).
require_once __DIR__ . '/models/ImageConverter.php';
require_once __DIR__ . '/controllers/ImageController.php';

// ── Dispatch ──────────────────────────────────────────────────────────────
$controller = new ImageController();
$controller->handle();