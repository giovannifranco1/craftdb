import _table from './data/_table.data.js';
import _canvas from './data/_canvas.data.js';
import _svgLines from './data/_svg-lines.js';

document.addEventListener(
    "alpine:init",
    () => {
        Alpine.data('runTable', _table);
        Alpine.data('runCanvas', _canvas);
        Alpine.data('runSvgLines', _svgLines);
    }
);
