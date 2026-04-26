<?php
// admin/_form.php - återanvänt formulär för create/edit Ⓐ Style
declare(strict_types=1);
/** @var array $todo  Aktuell post (eller default-array) */
/** @var string $action  URL till formulärets target */
/** @var string $submit_label */
?>
<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" class="form-card">
    <?= csrf_field() ?>
    <?php if (!empty($todo['id'])): ?>
        <input type="hidden" name="id" value="<?= e($todo['id']) ?>">
    <?php endif; ?>

    <div class="grid">
        <label class="span-2">Rubrik *
            <input type="text" name="rubrik" required maxlength="200" value="<?= e($todo['rubrik'] ?? '') ?>">
        </label>

        <label>Status
            <select name="status">
                <?php foreach (STATUSAR as $s): ?>
                    <option value="<?= e($s) ?>" <?= ($todo['status'] ?? 'ny') === $s ? 'selected' : '' ?>><?= e(status_label($s)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>Kategori
            <input type="text" name="kategori" maxlength="80" value="<?= e($todo['kategori'] ?? '') ?>">
        </label>
        <label>Underkategori
            <input type="text" name="underkategori" maxlength="80" value="<?= e($todo['underkategori'] ?? '') ?>">
        </label>

        <label>Datum
            <input type="date" name="datum" value="<?= e(substr((string)($todo['datum'] ?? ''), 0, 10)) ?>">
        </label>
        <label>Plats
            <input type="text" name="plats" maxlength="120" value="<?= e($todo['plats'] ?? '') ?>">
        </label>

        <label>Datum start
            <input type="datetime-local" name="datum_start" value="<?= e(format_local($todo['datum_start'] ?? null)) ?>">
        </label>
        <label>Datum slut
            <input type="datetime-local" name="datum_slut" value="<?= e(format_local($todo['datum_slut'] ?? null)) ?>">
        </label>

        <label class="span-2">Text
            <textarea name="text" rows="5" maxlength="4000"><?= e($todo['text'] ?? '') ?></textarea>
        </label>

        <label class="span-2">Bilder (kan välja flera)
            <input type="file" name="bild[]" accept="image/*" multiple>
        </label>
        <?php if (!empty($todo['bild'])): ?>
            <div class="span-2 thumbs">
                <?php foreach ($todo['bild'] as $b): ?>
                    <figure>
                        <img src="<?= e(url($b)) ?>" alt="">
                        <figcaption>
                            <label class="inline-check"><input type="checkbox" name="remove_bild[]" value="<?= e($b) ?>"> Ta bort</label>
                        </figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <label class="span-2">Ljud (kan välja flera)
            <input type="file" name="ljud[]" accept="audio/*" multiple>
        </label>
        <?php if (!empty($todo['ljud'])): ?>
            <ul class="span-2 file-list">
                <?php foreach ($todo['ljud'] as $a): ?>
                    <li>
                        <audio controls src="<?= e(url($a)) ?>"></audio>
                        <label class="inline-check"><input type="checkbox" name="remove_ljud[]" value="<?= e($a) ?>"> Ta bort</label>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <label class="span-2">Film (kan välja flera)
            <input type="file" name="film[]" accept="video/*" multiple>
        </label>
        <?php if (!empty($todo['film'])): ?>
            <ul class="span-2 file-list">
                <?php foreach ($todo['film'] as $v): ?>
                    <li>
                        <video controls src="<?= e(url($v)) ?>" style="max-width:240px"></video>
                        <label class="inline-check"><input type="checkbox" name="remove_film[]" value="<?= e($v) ?>"> Ta bort</label>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <label class="checkbox-row">
            <input type="checkbox" name="aktiv" value="1" <?= !empty($todo['aktiv']) ? 'checked' : '' ?>> Aktiv
        </label>
        <label class="checkbox-row">
            <input type="checkbox" name="arkiv" value="1" <?= !empty($todo['arkiv']) ? 'checked' : '' ?>> Arkiverad
        </label>
    </div>

    <div class="form-actions">
        <button class="btn btn-primary" type="submit"><?= e($submit_label) ?></button>
        <a class="btn" href="<?= e(url('admin/index.php')) ?>">Avbryt</a>
    </div>
</form>
