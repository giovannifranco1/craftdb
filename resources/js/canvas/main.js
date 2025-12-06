import _table from './data/_table.data.js';
import _area from './data/_area.data.js';
import _canvas from './data/_canvas.data.js';
import _svgLines from './data/_svg-lines.js';
import _foreignKeysGraph from './data/_foreign-keys-graph.js';
import _diagramGraph from './data/_diagram-graph.js';

document.addEventListener(
    "alpine:init",
    () => {
        Alpine.data('runTable', _table);
        Alpine.data('runArea', _area);
        Alpine.data('runCanvas', _canvas);
        Alpine.data('runSvgLines', _svgLines);
        Alpine.data('foreignKeysGraph', _foreignKeysGraph);
        Alpine.data('diagramGraph', _diagramGraph);
    }
);
