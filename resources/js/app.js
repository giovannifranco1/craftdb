import collapse from "@alpinejs/collapse";
import anchor from "@alpinejs/anchor";
import '@preline/tabs';

import.meta.glob(["../images/**"]);

document.addEventListener(
    "alpine:init",
    () => {
        const modules = import.meta.glob("./plugins/**/*.js", { eager: true });

        for (const path in modules) {
            window.Alpine.plugin(modules[path].default);
        }

        Alpine.plugin(collapse);
        Alpine.plugin(anchor);
    },
    { once: true },
);
