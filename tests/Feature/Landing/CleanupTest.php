<?php

it('LP13: tailwind.config.js is gone and its unused devDependencies are removed', function () {
    expect(file_exists(base_path('tailwind.config.js')))->toBeFalse();

    $package = json_decode(file_get_contents(base_path('package.json')), true);
    $dev = array_keys($package['devDependencies'] ?? []);

    foreach (['@tailwindcss/forms', 'tailwindcss-animate', 'daisyui'] as $dep) {
        expect($dev)->not->toContain($dep);
    }
});

it('LP13: filepond.css has no loose text outside comments', function () {
    $css = file_get_contents(resource_path('css/filepond.css'));
    $stripped = preg_replace('#/\*.*?\*/#s', '', $css);

    expect(preg_match('/;[ \t]*[A-Za-zÀ-ú]/u', $stripped))->toBe(0);
});
