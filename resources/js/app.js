import collapse from "@alpinejs/collapse";
import anchor from "@alpinejs/anchor";
import '@preline/tabs';
import '@preline/dropdown';
import '@floating-ui/dom';
import _diagramGraph from './canvas/data/_diagram-graph.js';
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

        Alpine.data('diagramGraph', _diagramGraph);
    },
    { once: true },
);

window.DEBUG_TABLE_DRAG = true;
