<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($t['title_line1']) ?> <?= htmlspecialchars($t['title_line2']) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Syne:wght@400;600;800&display=swap" rel="stylesheet">
<style>
:root {
  --bg:      #0b0c10;
  --surface: #13151c;
  --card:    #1a1d27;
  --border:  #2a2d3e;
  --accent:  #7fffb2;
  --accent2: #5bc8ff;
  --text:    #e8eaf0;
  --muted:   #6b7194;
  --danger:  #ff6b8a;
  --radius:  14px;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }
body {
  background: var(--bg);
  color: var(--text);
  font-family: 'Syne', sans-serif;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 48px 16px 80px;
  position: relative;
  overflow-x: hidden;
}
body::before {
  content: '';
  position: fixed; inset: 0;
  background-image:
    linear-gradient(rgba(127,255,178,.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(127,255,178,.03) 1px, transparent 1px);
  background-size: 40px 40px;
  pointer-events: none;
  z-index: 0;
}
body::after {
  content: '';
  position: fixed;
  top: -200px; left: 50%; transform: translateX(-50%);
  width: 800px; height: 400px;
  background: radial-gradient(ellipse, rgba(127,255,178,.07) 0%, transparent 70%);
  pointer-events: none;
  z-index: 0;
}

/* ── Lang switcher ── */
.lang-bar {
  position: fixed;
  top: 16px; right: 20px;
  display: flex; gap: 6px; align-items: center;
  z-index: 100;
  animation: fadeDown .5s ease both;
}
.lang-bar span {
  font-family: 'DM Mono', monospace;
  font-size: .65rem;
  letter-spacing: .1em;
  text-transform: uppercase;
  color: var(--muted);
  margin-right: 2px;
}
.lang-btn {
  background: var(--surface);
  border: 1px solid var(--border);
  color: var(--muted);
  font-family: 'DM Mono', monospace;
  font-size: .7rem;
  letter-spacing: .06em;
  padding: 5px 10px;
  border-radius: 6px;
  cursor: pointer;
  text-decoration: none;
  transition: border-color .2s, color .2s, background .2s;
  text-transform: uppercase;
}
.lang-btn:hover, .lang-btn.active {
  border-color: var(--accent);
  color: var(--accent);
  background: rgba(127,255,178,.06);
}

/* ── Header ── */
header {
  text-align: center;
  margin-bottom: 48px;
  position: relative; z-index: 1;
  animation: fadeDown .6s ease both;
}
header .badge {
  display: inline-block;
  background: rgba(127,255,178,.08);
  border: 1px solid rgba(127,255,178,.2);
  color: var(--accent);
  font-family: 'DM Mono', monospace;
  font-size: .7rem;
  letter-spacing: .15em;
  padding: 4px 12px;
  border-radius: 999px;
  margin-bottom: 18px;
  text-transform: uppercase;
}
h1 {
  font-size: clamp(2.4rem, 6vw, 4rem);
  font-weight: 800;
  line-height: 1.05;
  letter-spacing: -.03em;
}
h1 span { color: var(--accent); }
header p {
  color: var(--muted);
  font-size: 1rem;
  margin-top: 10px;
  font-weight: 400;
}

/* ── Main card ── */
.card {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  width: 100%;
  max-width: 680px;
  padding: 36px;
  position: relative; z-index: 1;
  animation: fadeUp .7s ease both .1s;
  box-shadow: 0 0 80px rgba(0,0,0,.5), inset 0 1px 0 rgba(255,255,255,.04);
}

/* ── Drop zone ── */
.drop-zone {
  border: 2px dashed var(--border);
  border-radius: 10px;
  padding: 48px 24px;
  text-align: center;
  cursor: pointer;
  transition: border-color .25s, background .25s, transform .2s;
  position: relative;
  overflow: hidden;
}
.drop-zone:hover, .drop-zone.drag-over {
  border-color: var(--accent);
  background: rgba(127,255,178,.04);
  transform: scale(1.005);
}
.drop-zone input[type="file"] {
  position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
}
.drop-icon {
  width: 52px; height: 52px;
  margin: 0 auto 14px;
  background: rgba(127,255,178,.08);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.5rem;
  transition: transform .3s;
}
.drop-zone:hover .drop-icon { transform: translateY(-4px) scale(1.05); }
.drop-zone .drop-title { font-size: 1rem; font-weight: 600; margin-bottom: 4px; }
.drop-zone .drop-sub   { font-size: .8rem; color: var(--muted); font-family: 'DM Mono', monospace; }

/* ── File preview pill ── */
#file-info {
  display: none;
  align-items: center;
  gap: 12px;
  margin-top: 14px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 10px 14px;
  font-size: .82rem;
  font-family: 'DM Mono', monospace;
}
#file-info img { width: 36px; height: 36px; object-fit: cover; border-radius: 5px; }
#file-info .fi-name { color: var(--text); font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; }
#file-info .fi-size { color: var(--muted); white-space: nowrap; }

/* ── Options row ── */
.options-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-top: 22px;
}
label.field-label {
  display: block;
  font-size: .72rem;
  font-family: 'DM Mono', monospace;
  letter-spacing: .08em;
  text-transform: uppercase;
  color: var(--muted);
  margin-bottom: 8px;
}
select {
  width: 100%;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 8px;
  color: var(--text);
  font-family: 'DM Mono', monospace;
  font-size: .88rem;
  padding: 10px 12px;
  appearance: none;
  outline: none;
  transition: border-color .2s;
  cursor: pointer;
}
select:focus { border-color: var(--accent); }
select option { background: var(--card); }
.select-wrap { position: relative; }
.select-wrap::after {
  content: '▾';
  position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
  color: var(--muted); pointer-events: none; font-size: .8rem;
}
.range-wrap { display: flex; flex-direction: column; }
input[type="range"] {
  width: 100%; padding: 0; height: 4px;
  accent-color: var(--accent);
  border: none; background: var(--border);
  border-radius: 999px; cursor: pointer;
  margin-top: 4px;
  appearance: none; outline: none;
}
.range-labels { display: flex; justify-content: space-between; margin-top: 6px; }
.range-labels span { font-size: .7rem; font-family: 'DM Mono', monospace; color: var(--muted); }
#quality-val {
  color: var(--accent);
  font-family: 'DM Mono', monospace;
  font-weight: 500;
  font-size: .82rem;
}

/* ── Convert button ── */
.btn-convert {
  width: 100%; margin-top: 26px; padding: 15px; border: none;
  border-radius: 10px; background: var(--accent); color: #0b0c10;
  font-family: 'Syne', sans-serif; font-weight: 700; font-size: 1rem;
  letter-spacing: .03em; cursor: pointer;
  transition: opacity .2s, transform .15s, box-shadow .2s;
  box-shadow: 0 0 30px rgba(127,255,178,.2);
}
.btn-convert:hover { opacity: .88; transform: translateY(-1px); box-shadow: 0 4px 40px rgba(127,255,178,.3); }
.btn-convert:active { transform: scale(.98); }

/* ── Error alert ── */
.alert-error {
  background: rgba(255,107,138,.08); border: 1px solid rgba(255,107,138,.3);
  color: var(--danger); border-radius: 8px; padding: 12px 16px;
  font-size: .85rem; margin-top: 20px;
  font-family: 'DM Mono', monospace;
  animation: fadeUp .3s ease;
}

/* ── Result card ── */
.result-card {
  background: var(--card); border: 1px solid var(--border);
  border-radius: var(--radius); width: 100%; max-width: 680px;
  padding: 30px 36px; position: relative; z-index: 1; margin-top: 24px;
  animation: fadeUp .5s ease both;
  box-shadow: 0 0 80px rgba(0,0,0,.4);
}
.result-header { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
.result-header .dot {
  width: 8px; height: 8px; background: var(--accent); border-radius: 50%;
  box-shadow: 0 0 10px var(--accent); animation: pulse 1.5s infinite;
}
.result-header h2 { font-size: 1rem; font-weight: 600; }
.preview-img {
  width: 100%; max-height: 320px; object-fit: contain; border-radius: 8px;
  background: repeating-conic-gradient(#1e2030 0% 25%, #252839 0% 50%) 0 0 / 16px 16px;
  display: block; margin-bottom: 18px; border: 1px solid var(--border);
}
.meta-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 20px; }
.meta-item { background: var(--surface); border-radius: 8px; padding: 10px 12px; text-align: center; }
.meta-item .mi-val { font-family: 'DM Mono', monospace; font-weight: 500; font-size: .9rem; color: var(--accent2); }
.meta-item .mi-key { font-size: .68rem; font-family: 'DM Mono', monospace; color: var(--muted); text-transform: uppercase; margin-top: 3px; letter-spacing: .06em; }
.btn-download {
  width: 100%; padding: 14px; border-radius: 10px; border: 1px solid var(--accent);
  background: transparent; color: var(--accent); font-family: 'Syne', sans-serif;
  font-weight: 700; font-size: .95rem; cursor: pointer; text-align: center;
  text-decoration: none; display: block;
  transition: background .2s, color .2s, transform .15s;
  letter-spacing: .03em;
}
.btn-download:hover { background: var(--accent); color: #0b0c10; transform: translateY(-1px); }

/* ── Formats bar ── */
.formats-bar {
  display: flex; flex-wrap: wrap; gap: 8px; justify-content: center;
  margin-top: 40px; position: relative; z-index: 1;
  animation: fadeUp .8s ease both .3s;
}
.fmt-tag {
  background: var(--surface); border: 1px solid var(--border); border-radius: 6px;
  padding: 4px 10px; font-size: .7rem; font-family: 'DM Mono', monospace;
  color: var(--muted); text-transform: uppercase; letter-spacing: .06em;
}

/* ── Animations ── */
@keyframes fadeDown { from { opacity:0; transform:translateY(-20px); } to { opacity:1; transform:none; } }
@keyframes fadeUp   { from { opacity:0; transform:translateY(20px);  } to { opacity:1; transform:none; } }
@keyframes pulse    { 0%,100%{ opacity:1; } 50%{ opacity:.3; } }

@media (max-width: 520px) {
  .card, .result-card { padding: 22px 18px; }
  .options-row { grid-template-columns: 1fr; }
  .lang-bar { top: 10px; right: 10px; }
  .lang-bar span { display: none; }
}
</style>
</head>
<body>

<!-- ── Language switcher ── -->
<nav class="lang-bar" aria-label="<?= htmlspecialchars($t['lang_label']) ?>">
  <span><?= htmlspecialchars($t['lang_label']) ?>:</span>
  <?php foreach (['en' => 'EN', 'pt_BR' => 'PT', 'es' => 'ES'] as $code => $label): ?>
    <a href="?lang=<?= $code ?>"
       class="lang-btn<?= $lang === $code ? ' active' : '' ?>"
       hreflang="<?= $code ?>"><?= $label ?></a>
  <?php endforeach; ?>
</nav>

<!-- ── Header ── -->
<header>
  <div class="badge"><?= htmlspecialchars($t['badge']) ?></div>
  <h1><?= htmlspecialchars($t['title_line1']) ?><br><span><?= htmlspecialchars($t['title_line2']) ?></span></h1>
  <p><?= htmlspecialchars($t['subtitle']) ?></p>
</header>

<!-- ── Upload form ── -->
<div class="card">
  <form method="post" enctype="multipart/form-data" id="conv-form">
    <input type="hidden" name="lang" value="<?= htmlspecialchars($lang) ?>">

    <div class="drop-zone" id="drop-zone">
      <input type="file" name="image" id="file-input" accept="image/*" required>
      <div class="drop-icon">🖼️</div>
      <div class="drop-title"><?= htmlspecialchars($t['drop_title']) ?></div>
      <div class="drop-sub"><?= $t['drop_sub'] ?></div>
    </div>

    <div id="file-info">
      <img id="fi-thumb" src="" alt="">
      <span class="fi-name" id="fi-name">—</span>
      <span class="fi-size" id="fi-size">—</span>
    </div>

    <div class="options-row">
      <div>
        <label class="field-label" for="format"><?= htmlspecialchars($t['convert_to']) ?></label>
        <div class="select-wrap">
          <select name="format" id="format">
            <?php
            $formats    = ['png' => 'PNG', 'jpg' => 'JPG / JPEG', 'webp' => 'WebP', 'gif' => 'GIF', 'bmp' => 'BMP'];
            $selFormat  = $_POST['format'] ?? 'png';
            foreach ($formats as $val => $label):
            ?>
              <option value="<?= $val ?>"<?= $selFormat === $val ? ' selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="range-wrap">
        <label class="field-label" for="quality">
          <?= htmlspecialchars($t['quality']) ?> — <span id="quality-val"><?= (int)($_POST['quality'] ?? 90) ?>%</span>
        </label>
        <input type="range" name="quality" id="quality"
               min="1" max="100" value="<?= (int)($_POST['quality'] ?? 90) ?>">
        <div class="range-labels">
          <span><?= htmlspecialchars($t['quality_lower']) ?></span>
          <span><?= htmlspecialchars($t['quality_higher']) ?></span>
        </div>
      </div>
    </div>

    <button type="submit" class="btn-convert"><?= htmlspecialchars($t['btn_convert']) ?></button>

    <?php if ($error): ?>
      <div class="alert-error" role="alert">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
  </form>
</div>

<!-- ── Conversion result ── -->
<?php if ($result): ?>
<div class="result-card" id="result">
  <div class="result-header">
    <div class="dot"></div>
    <h2>
      <?= htmlspecialchars($t['result_done']) ?> —
      <span style="font-family:'DM Mono',monospace;color:var(--accent)">
        <?= htmlspecialchars($result['name']) ?>
      </span>
    </h2>
  </div>

  <img class="preview-img" src="<?= $preview ?>" alt="<?= htmlspecialchars($result['name']) ?>">

  <div class="meta-grid">
    <div class="meta-item">
      <div class="mi-val"><?= strtoupper(pathinfo($result['name'], PATHINFO_EXTENSION)) ?></div>
      <div class="mi-key"><?= htmlspecialchars($t['fmt_label']) ?></div>
    </div>
    <div class="meta-item">
      <div class="mi-val"><?= $result['w'] ?>×<?= $result['h'] ?></div>
      <div class="mi-key"><?= htmlspecialchars($t['dim_label']) ?></div>
    </div>
    <div class="meta-item">
      <div class="mi-val"><?= ImageConverter::formatBytes($result['size']) ?></div>
      <div class="mi-key"><?= htmlspecialchars($t['size_label']) ?></div>
    </div>
  </div>

  <a class="btn-download"
     href="data:<?= $result['mime'] ?>;base64,<?= $result['b64'] ?>"
     download="<?= htmlspecialchars($result['name']) ?>">
    <?= htmlspecialchars($t['btn_download']) ?> <?= htmlspecialchars($result['name']) ?>
  </a>
</div>
<?php endif; ?>

<!-- ── Formats footer ── -->
<div class="formats-bar" aria-label="<?= htmlspecialchars($t['formats_title']) ?>">
  <?php foreach (['JPEG','PNG','WebP','GIF','BMP','AVIF'] as $f): ?>
    <span class="fmt-tag"><?= $f ?></span>
  <?php endforeach; ?>
</div>

<script>
const input    = document.getElementById('file-input');
const dropZone = document.getElementById('drop-zone');
const fileInfo = document.getElementById('file-info');
const fiThumb  = document.getElementById('fi-thumb');
const fiName   = document.getElementById('fi-name');
const fiSize   = document.getElementById('fi-size');
const quality  = document.getElementById('quality');
const qVal     = document.getElementById('quality-val');

function fmtBytes(b) {
  if (b < 1024)        return b + ' B';
  if (b < 1_048_576)   return (b / 1024).toFixed(1) + ' KB';
  return (b / 1_048_576).toFixed(1) + ' MB';
}

function showPreview(file) {
  fiName.textContent = file.name;
  fiSize.textContent = fmtBytes(file.size);
  const reader = new FileReader();
  reader.onload = e => { fiThumb.src = e.target.result; };
  reader.readAsDataURL(file);
  fileInfo.style.display = 'flex';
}

input.addEventListener('change', () => { if (input.files[0]) showPreview(input.files[0]); });

['dragenter', 'dragover'].forEach(ev =>
  dropZone.addEventListener(ev, e => { e.preventDefault(); dropZone.classList.add('drag-over'); }));
['dragleave', 'drop'].forEach(ev =>
  dropZone.addEventListener(ev, e => { e.preventDefault(); dropZone.classList.remove('drag-over'); }));
dropZone.addEventListener('drop', e => {
  const file = e.dataTransfer.files[0];
  if (file) { const dt = new DataTransfer(); dt.items.add(file); input.files = dt.files; showPreview(file); }
});

quality.addEventListener('input', () => { qVal.textContent = quality.value + '%'; });

<?php if ($result): ?>
document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('result')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
});
<?php endif; ?>
</script>
</body>
</html>
