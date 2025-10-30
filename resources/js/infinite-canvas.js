export class InfiniteCanvas {
    canvas = null;
    tablesContainer = null;
    context = null;
    cellSize;

    #scale = 1;
    #offsetX = 0;
    #offsetY = 0;

    #touchMode = "single"; // "single" | "double"
    #prevTouch = [null, null]; // [Touch | null, Touch | null]

    constructor(cellSize = 40) {
        this.cellSize = cellSize;

        const canvas = document.getElementById("canvas");

        if (canvas && canvas instanceof HTMLCanvasElement) {
            this.canvas = canvas;
            this.tablesContainer = document.querySelector('.tables');
            this.#setupEvents(canvas);

            const context = canvas.getContext("2d");

            if (context) {
                this.context = context;
                this.#draw();
            } else {
                console.error(`<canvas> element is missing context 2d`);
            }
        } else {
            console.error(`<canvas> element with id="canvas" not found`);
        }
    }

    toVirtualX(xReal) {
        return (xReal + this.#offsetX) * this.#scale;
    }
    toVirtualY(yReal) {
        return (yReal + this.#offsetY) * this.#scale;
    }
    toRealX(xVirtual) {
        return xVirtual / this.#scale - this.#offsetX;
    }
    toRealY(yVirtual) {
        return yVirtual / this.#scale - this.#offsetY;
    }

    virtualHeight() {
        return (this.canvas?.clientHeight ?? 0) / this.#scale;
    }

    virtualWidth() {
        return (this.canvas?.clientWidth ?? 0) / this.#scale;
    }

    zoom(amount) {
        this.#scale *= amount;
        this.#draw();
        window.dispatchEvent(new CustomEvent('canvas-zoom', { bubbles: true, detail: { scale: this.getScale() } }));
    }

    offsetLeft(amount) {
        this.#offsetX -= amount;
        this.#draw();
        this.#dispatchTransformChanged();
    }

    offsetRight(amount) {
        this.#offsetX += amount;
        this.#draw();
        this.#dispatchTransformChanged();

    }

    offsetUp(amount) {
        this.#offsetY -= amount;
        this.#draw();
        this.#dispatchTransformChanged();
    }

    offsetDown(amount) {
        this.#offsetY += amount;
        this.#draw();
        this.#dispatchTransformChanged();

    }

    getScale() {
        return this.#scale;
    }

    getTransformString() {
        const x = -this.#offsetX * this.#scale;
        const y = -this.#offsetY * this.#scale;
        return `translate(${x}, ${y}) scale(${this.#scale})`;
    }

    #dispatchTransformChanged() {
        const event = new CustomEvent('canvas-transform', {
            detail: {
                scale: this.getScale(),
                transform: this.getTransformString(), // tipo "translate(x, y) scale(s)"
            }
        });
        window.dispatchEvent(event);
    }

    #applyContainerTransform() {
        if (this.tablesContainer) {
            // A transformação pan/zoom deve compensar a posição do grid.
            // O grid move-se visualmente com offsetX e offsetY.
            // O conteúdo DOM deve mover-se na direção OPOSTA (-offsetX, -offsetY)
            // e aplicar a mesma escala.

            const transformX = -this.#offsetX * this.#scale;
            const transformY = -this.#offsetY * this.#scale;

            // Usamos transform3d para melhor performance (aceleração de hardware)
            this.tablesContainer.style.transform = `translate3d(${transformX}px, ${transformY}px, 0) scale(${this.#scale})`;
        }
    }

    #draw() {
        if (this.canvas && this.context) {
            this.canvas.width = document.body.clientWidth;
            this.canvas.height = document.body.clientHeight;
            this.context.clearRect(0, 0, this.canvas.width, this.canvas.height);
            this.#drawGrid();
            this.#applyContainerTransform();
        }
    }

    #setupEvents(canvas) {
        canvas.addEventListener("touchstart", (event) =>
            this.#onTouchStart(event.touches)
        );

        canvas.addEventListener("touchmove", (event) =>
            this.#onTouchMove(event.touches)
        );

        window.addEventListener("wheel", (event) => {
            event.preventDefault();
            this.#onWheel(event.deltaX, event.deltaY)
        }, { passive: false });

        window.addEventListener("resize", () => this.#draw());
    }

    #onWheel(deltaX, deltaY) {
        this.#offsetX += deltaX / this.#scale;
        this.#offsetY += deltaY / this.#scale;
        this.#draw();
        this.#dispatchTransformChanged();
    }

    #onTouchStart(touches) {
        if (touches.length === 1) {
            this.#touchMode = "single";
        } else if (touches.length >= 2) {
            this.#touchMode = "double";
        }

        // Store the last touches
        this.#prevTouch[0] = touches[0];
        this.#prevTouch[1] = touches[1];

        this.#onTouchMove(touches);
    }

    #onTouchMove(touches) {
        // Acessar as propriedades de pageX/Y do primeiro touch
        const touch0X = touches[0].pageX;
        const touch0Y = touches[0].pageY;

        // Usar o operador de encadeamento opcional e coalescência nula para o caso de `this.#prevTouch[0]` ser null (embora o `onTouchStart` garanta que não é, é bom para robustez)
        const prevTouch0X = this.#prevTouch[0]?.pageX ?? touch0X;
        const prevTouch0Y = this.#prevTouch[0]?.pageY ?? touch0Y;

        if (this.#touchMode === "single") {
            // Single touch (setup click event) - o código original não implementa o pan com um dedo, apenas configura o touchMode
        } else if (this.#touchMode === "double" && touches.length >= 2) {
            // get second touch coordinates
            const touch1X = touches[1].pageX;
            const touch1Y = touches[1].pageY;

            const prevTouch1X = this.#prevTouch[1]?.pageX ?? touch1X;
            const prevTouch1Y = this.#prevTouch[1]?.pageY ?? touch1Y;

            const scaleAmount = this.#zoom(
                [touch0X, touch0Y],
                [prevTouch0X, prevTouch0Y],
                [touch1X, touch1Y],
                [prevTouch1X, prevTouch1Y]
            );

            this.#pan(
                scaleAmount,
                [touch0X, touch0Y],
                [prevTouch0X, prevTouch0Y],
                [touch1X, touch1Y],
                [prevTouch1X, prevTouch1Y]
            );

            this.#draw();
        }

        // Store the current touches for the next move event
        this.#prevTouch[0] = touches[0] || null;
        this.#prevTouch[1] = touches[1] || null;
    }

    #pan(
        scaleAmount,
        [touch0X, touch0Y],
        [prevTouch0X, prevTouch0Y],
        [touch1X, touch1Y],
        [prevTouch1X, prevTouch1Y]
    ) {
        // get midpoints
        const midX = (touch0X + touch1X) / 2;
        const midY = (touch0Y + touch1Y) / 2;
        const prevMidX = (prevTouch0X + prevTouch1X) / 2;
        const prevMidY = (prevTouch0Y + prevTouch1Y) / 2;

        // Calculate how many pixels the midpoints have moved in the x and y direction
        const panX = midX - prevMidX;
        const panY = midY - prevMidY;

        // Scale this movement based on the zoom level
        this.#offsetX += panX / this.#scale;
        this.#offsetY += panY / this.#scale;

        // Get the relative position of the middle of the zoom.
        // 0, 0 would be top left.
        // 0, 1 would be top right etc.
        const zoomRatioX = midX / (this.canvas?.clientWidth ?? 1);
        const zoomRatioY = midY / (this.canvas?.clientHeight ?? 1);

        // calculate the amounts zoomed from each edge of the screen
        const unitsZoomedX = this.virtualWidth() * scaleAmount;
        const unitsZoomedY = this.virtualHeight() * scaleAmount;

        const unitsAddLeft = unitsZoomedX * zoomRatioX;
        const unitsAddTop = unitsZoomedY * zoomRatioY;

        this.#offsetX += unitsAddLeft;
        this.#offsetY += unitsAddTop;
    }

    #zoom(
        [touch0X, touch0Y],
        [prevTouch0X, prevTouch0Y],
        [touch1X, touch1Y],
        [prevTouch1X, prevTouch1Y]
    ) {
        // Calcula a distância atual entre os dois toques (hipotenusa)
        const hypot = Math.sqrt(
            Math.pow(touch0X - touch1X, 2) + Math.pow(touch0Y - touch1Y, 2)
        );

        // Calcula a distância anterior entre os dois toques
        const prevHypot = Math.sqrt(
            Math.pow(prevTouch0X - prevTouch1X, 2) +
                Math.pow(prevTouch0Y - prevTouch1Y, 2)
        );

        // Calcula o fator de zoom: quão mais longe ou perto os toques estão
        const zoomAmount = hypot / prevHypot;
        this.zoom(zoomAmount);

        // Retorna o inverso do zoomAmount para ser usado no #pan
        const scaleAmount = 1 - zoomAmount;
        return scaleAmount;
    }
    #drawGrid() {
        if (this.canvas && this.context) {
            this.context.strokeStyle = "rgb(229,231,235)";
            this.context.lineWidth = 1;
            this.context.font = "10px serif";
            this.context.beginPath();

            const width = this.canvas.clientWidth;
            const height = this.canvas.clientHeight;

            // Desenha as linhas verticais
            // O cálculo do ponto de partida deve levar em consideração o resto da divisão do offsetX pelo cellSize, escalado.
            const startX = (-this.#offsetX % this.cellSize) * this.#scale;

            for (let x = startX; x <= width; x += this.cellSize * this.#scale) {
                const source = x;
                this.context.moveTo(source, 0);
                this.context.lineTo(source, height);

                // Calcula a posição virtual para o texto
                const virtualX = this.toVirtualX(source / this.#scale);
                this.context.fillText(`${virtualX.toFixed(0)}`, source, 10);
            }

            // Desenha as linhas horizontais
            // O cálculo do ponto de partida deve levar em consideração o resto da divisão do offsetY pelo cellSize, escalado.
            const startY = (-this.#offsetY % this.cellSize) * this.#scale;

            for (
                let y = startY;
                y <= height;
                y += this.cellSize * this.#scale
            ) {
                const destination = y;
                this.context.moveTo(0, destination);
                this.context.lineTo(width, destination);

                // Calcula a posição virtual para o texto
                const virtualY = this.toVirtualY(destination / this.#scale);
                this.context.fillText(`${virtualY.toFixed(0)}`, 0, destination);
            }
            this.context.stroke();
        }
    }
}
