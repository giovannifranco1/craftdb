import { InfiniteCanvas } from '../classes/InfiniteCanvas.js'

// @Alpine.js data
export default (cellSize = 25) => ({
    canvas: new InfiniteCanvas(cellSize),
    zoomPercent: 75
});
