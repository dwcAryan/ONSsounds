<?php
/**
 * ONS SOUND - Reusable Section Heading Component
 */
function render_section_heading(string $title, ?string $subtitle = null, string $align = 'center'): void {
    $alignmentClass = match($align) {
        'left' => 'text-start',
        'right' => 'text-end',
        default => 'text-center'
    };
    ?>
    <div class="section-header <?= $alignmentClass ?> mb-5">
        <h2 class="section-title"><?= e($title) ?></h2>
        <?php if (!empty($subtitle)): ?>
            <p class="section-subtitle"><?= e($subtitle) ?></p>
        <?php endif; ?>
    </div>
    <?php
}
