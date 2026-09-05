<?php
/**
 * Sections editor (create + manage).
 *
 * @var list<array{id: string, title: string, levels: list<string>}> $sections
 * @package Romanttinen_Kutsu
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!isset($sections) || !is_array($sections) || $sections === []) {
    $sections = [Romant_Kutsu_CPT::empty_section()];
}

$max_sec = Romant_Kutsu_CPT::MAX_SECTIONS;
$soft    = Romant_Kutsu_CPT::SOFT_MAX_LEVELS;
$hard    = Romant_Kutsu_CPT::HARD_MAX_LEVELS;
?>
<fieldset class="romant-sections-fieldset" data-romant-sections-editor
          data-max-sections="<?php echo esc_attr((string) $max_sec); ?>"
          data-soft-max="<?php echo esc_attr((string) $soft); ?>"
          data-hard-max="<?php echo esc_attr((string) $hard); ?>">
    <legend>Osiot (peli)</legend>
    <p class="romant-hint">
        Enintään <?php echo esc_html((string) $max_sec); ?> osiota (esim. Leffa / Ruoka / Koti).
        Jokaisessa oletuksena 3 tasoa — vastaanottaja avaa niitä yksi kerrallaan.
    </p>

    <div class="romant-sections-list" data-sections-list>
        <?php foreach ($sections as $si => $sec) :
            $sid    = (string) ($sec['id'] ?? '');
            $stitle = (string) ($sec['title'] ?? '');
            $levels = $sec['levels'] ?? ['', '', ''];
            if (!is_array($levels) || $levels === []) {
                $levels = ['', '', ''];
            }
            while (count($levels) < 3) {
                $levels[] = '';
            }
            ?>
            <div class="romant-section-card" data-section-card data-index="<?php echo esc_attr((string) $si); ?>">
                <div class="romant-section-card-head">
                    <label class="romant-section-title-label">
                        Osion otsikko
                        <input type="hidden" name="romant_sections[<?php echo esc_attr((string) $si); ?>][id]"
                               value="<?php echo esc_attr($sid); ?>" data-section-id />
                        <input type="text" name="romant_sections[<?php echo esc_attr((string) $si); ?>][title]"
                               value="<?php echo esc_attr($stitle); ?>" maxlength="80"
                               placeholder="Esim. Leffa" required data-section-title />
                    </label>
                    <button type="button" class="romant-btn romant-btn-ghost romant-btn-sm" data-remove-section
                            <?php echo count($sections) <= 1 ? ' hidden' : ''; ?>>
                        Poista osio
                    </button>
                </div>
                <div class="romant-section-levels" data-section-levels>
                    <?php foreach ($levels as $li => $lv) : ?>
                        <div class="romant-level-row" data-level-row>
                            <label>
                                <span data-level-label>Taso <?php echo esc_html((string) ($li + 1)); ?></span>
                                <span class="req" data-level-req<?php echo $li === 0 ? '' : ' hidden'; ?>>*</span>
                                <textarea name="romant_sections[<?php echo esc_attr((string) $si); ?>][levels][<?php echo esc_attr((string) $li); ?>]"
                                          rows="2" maxlength="800"
                                          placeholder="Kirjoita tämän tason teksti…"
                                          <?php echo $li === 0 ? 'required' : ''; ?>
                                          data-level-text><?php echo esc_textarea((string) $lv); ?></textarea>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p class="romant-soft-cap-warn" data-soft-cap-warn hidden>Pehmeä raja (10) ylitetty — pidä tasot maltillisina.</p>
                <button type="button" class="romant-btn romant-btn-secondary romant-btn-sm" data-add-level>
                    Lisää taso
                </button>
            </div>
        <?php endforeach; ?>
    </div>

    <button type="button" class="romant-btn romant-btn-ghost" data-add-section
            <?php echo count($sections) >= $max_sec ? ' hidden' : ''; ?>>
        Lisää osio
    </button>
</fieldset>
