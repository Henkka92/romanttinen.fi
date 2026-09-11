<?php
/**
 * Sections editor (create + manage) — cream cards, curated chips, no free planner.
 *
 * @var list<array{id: string, title: string, levels: list<string>}> $sections
 * @var bool $craft_editor  Portti 2 create: collapsed cards + pohja world.
 * @package Romanttinen_Kutsu
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!isset($sections) || !is_array($sections) || $sections === []) {
    $sections = Romant_Kutsu_CPT::default_craft_sections();
}

$craft_editor = !empty($craft_editor);
$max_sec      = Romant_Kutsu_CPT::MAX_SECTIONS;
$soft         = Romant_Kutsu_CPT::SOFT_MAX_LEVELS;
$hard         = Romant_Kutsu_CPT::HARD_MAX_LEVELS;
$ph_empty     = Romant_Kutsu_CPT::PLACEHOLDER_EMPTY;
$ph_l1        = Romant_Kutsu_CPT::PLACEHOLDER_L1;
$ph_l2        = Romant_Kutsu_CPT::PLACEHOLDER_L2;
$used_titles  = [];
foreach ($sections as $sec) {
    $used_titles[] = (string) ($sec['title'] ?? '');
}
$chip_titles = [];
foreach (Romant_Kutsu_CPT::EXTRA_SECTION_TITLES as $chip) {
    if (!in_array($chip, $used_titles, true)) {
        $chip_titles[] = $chip;
    }
}
if (!$craft_editor) {
    foreach (Romant_Kutsu_CPT::DEFAULT_SECTION_TITLES as $chip) {
        if (!in_array($chip, $used_titles, true)) {
            $chip_titles[] = $chip;
        }
    }
}
?>
<div class="romant-sections-fieldset<?php echo $craft_editor ? ' is-craft' : ''; ?>" data-romant-sections-editor
     data-romant-craft="<?php echo $craft_editor ? '1' : '0'; ?>"
     data-max-sections="<?php echo esc_attr((string) $max_sec); ?>"
     data-soft-max="<?php echo esc_attr((string) $soft); ?>"
     data-hard-max="<?php echo esc_attr((string) $hard); ?>">

    <div class="romant-hetket-head">
        <p class="romant-kicker">Kutsun hetket</p>
        <p class="romant-hetket-count" data-oletus-count><?php echo esc_html((string) count($sections)); ?> oletusta</p>
    </div>

    <div class="romant-sections-list" data-sections-list>
        <?php foreach ($sections as $si => $sec) :
            $sid    = (string) ($sec['id'] ?? '');
            $stitle = (string) ($sec['title'] ?? '');
            $levels = $sec['levels'] ?? ['', '', ''];
            if (!is_array($levels) || $levels === []) {
                $levels = ['', '', ''];
            }
            while (count($levels) < 1) {
                $levels[] = '';
            }
            $l1         = (string) ($levels[0] ?? '');
            $is_default = Romant_Kutsu_CPT::is_default_section_title($stitle);
            $example    = Romant_Kutsu_CPT::example_l1($stitle, 'kotitreffit', false);
            ?>
            <div class="romant-section-card romant-card<?php echo $craft_editor ? ' is-collapsed' : ''; ?>"
                 data-section-card data-index="<?php echo esc_attr((string) $si); ?>">
                <div class="romant-section-card-head">
                    <span class="romant-section-num" data-section-num aria-hidden="true"><?php echo esc_html((string) ($si + 1)); ?></span>
                    <label class="romant-section-title-label">
                        <span class="screen-reader-text">Osion otsikko</span>
                        <input type="hidden" name="romant_sections[<?php echo esc_attr((string) $si); ?>][id]"
                               value="<?php echo esc_attr($sid); ?>" data-section-id />
                        <input type="text" name="romant_sections[<?php echo esc_attr((string) $si); ?>][title]"
                               value="<?php echo esc_attr($stitle); ?>" maxlength="80"
                               placeholder="Elokuvahetki" required data-section-title />
                    </label>
                    <span class="romant-oletus" data-oletus-badge<?php echo $is_default ? '' : ' hidden'; ?>>Oletus</span>
                    <button type="button" class="romant-btn romant-btn-ghost romant-btn-sm" data-remove-section
                            <?php echo count($sections) <= 1 ? ' hidden' : ''; ?>>
                        Poista
                    </button>
                </div>
                <p class="romant-section-preview" data-section-preview><?php echo esc_html($l1); ?></p>
                <div class="romant-section-levels" data-section-levels>
                    <?php foreach ($levels as $li => $lv) :
                        $ph = $li === 0 ? $ph_l1 : $ph_l2;
                        if ((string) $lv === '' && $li === 0) {
                            $ph = $ph_empty;
                        }
                        ?>
                        <div class="romant-level-row<?php echo $craft_editor && $li > 0 && (string) $lv === '' ? ' is-quiet' : ''; ?>" data-level-row>
                            <label>
                                <span data-level-label><?php echo $li === 0 ? 'Vihje' : 'Taso ' . (string) ($li + 1); ?></span>
                                <span class="req" data-level-req<?php echo $li === 0 ? '' : ' hidden'; ?>>*</span>
                                <textarea name="romant_sections[<?php echo esc_attr((string) $si); ?>][levels][<?php echo esc_attr((string) $li); ?>]"
                                          rows="2" maxlength="800"
                                          placeholder="<?php echo esc_attr($ph); ?>"
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
                <div class="romant-section-foot">
                    <button type="button" class="romant-use-example" data-use-example
                            <?php echo $example === '' ? ' hidden' : ''; ?>>
                        Käytä esimerkkiä
                    </button>
                    <p class="romant-napauta" data-napauta>napauta muokataksesi</p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <p class="romant-hint romant-craft-help">Voit muokata sisältöä vapaasti.</p>

    <div class="romant-section-chips" data-section-chips <?php echo $chip_titles === [] ? ' hidden' : ''; ?>>
        <?php foreach ($chip_titles as $chip) : ?>
            <button type="button" class="romant-chip" data-add-chip="<?php echo esc_attr($chip); ?>"
                    <?php echo count($sections) >= $max_sec ? ' disabled' : ''; ?>>
                <?php echo esc_html($chip); ?>
            </button>
        <?php endforeach; ?>
    </div>
</div>
