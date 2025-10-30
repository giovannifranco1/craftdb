import collapse from "@alpinejs/collapse";
import anchor from "@alpinejs/anchor";
import { InfiniteCanvas } from '../js/infinite-canvas.js'

import.meta.glob(["../images/**"]);

document.addEventListener(
    "alpine:init",
    () => {
        const modules = import.meta.glob("./plugins/**/*.js", { eager: true });

        for (const path in modules) {
            window.Alpine.plugin(modules[path].default);
        }
        window.Alpine.plugin(collapse);
        window.Alpine.plugin(anchor);
    },
    { once: true },
);

window.InfiniteCanvas = InfiniteCanvas;
