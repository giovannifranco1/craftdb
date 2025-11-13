export class InfiniteCanvas {
    canvas = null;
    tablesContainer = null;
    context = null;
    cellSize;

    #scale = 0.95;
    #offsetX = 0;
    #offsetY = 0;

    #touchMode = "single"; // "single" | "double"
    #prevTouch = [null, null]; // [Touch | null, Touch | null]

    constructor(cellSize = 40, [offsetX, offsetY] = []) {
        this.cellSize = cellSize;

        const canvas = document.getElementById("canvas");

        if (canvas && canvas instanceof HTMLCanvasElement) {
            this.canvas = canvas;
            this.tablesContainer = document.querySelector('.tables');
            this.#setupEvents(canvas);

            const context = canvas.getContext("2d");

            if (context) {
                this.context = context;
                // Se offsetX e offsetY são números válidos, ajusta os offsets iniciais
                if (typeof offsetX === 'number' && typeof offsetY === 'number') {
                    this.#offsetX = offsetX / this.#scale;
                    this.#offsetY = offsetY / this.#scale;
                }

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

    setPositionOffsets(offsetX, offsetY) {
        if (typeof offsetX === 'number' && typeof offsetY === 'number') {
            this.#offsetX = offsetX;
            this.#offsetY = offsetY;
        }
        this.#draw();
        this.#dispatchTransformChanged();
    }

    getScale() {
        return this.#scale;
    }

    getOffsetX() {
        return this.#offsetX;
    }

    getOffsetY() {
        return this.#offsetY;
    }

    clientToVirtual(clientX, clientY) {
        if (!this.canvas) {
            return { x: 0, y: 0 };
        }

        const rect = this.canvas.getBoundingClientRect();
        const xRelative = clientX - rect.left;
        const yRelative = clientY - rect.top;

        const scale = this.getScale();
        const offsetX = this.getOffsetX();
        const offsetY = this.getOffsetY();

        const virtualX = xRelative / scale + offsetX;
        const virtualY = yRelative / scale + offsetY;

        return { x: virtualX, y: virtualY };
    }

    getTransformString() {
        const x = -this.#offsetX * this.#scale;
        const y = -this.#offsetY * this.#scale;
        return `translate(${x}, ${y}) scale(${this.#scale})`;
    }

    #dispatchTransformChanged() {
        const event = new CustomEvent('canvas::transform', {
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
            const sidePanel = event.target.closest('section[aria-modal="true"]');

            if (sidePanel) {
                return; // Não processa eventos de wheel dentro do painel lateral
            }

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
            const step = this.cellSize * this.#scale;

            // Cálculo do ponto de partida leva em conta o offset escalado
            const startX = (-this.#offsetX % this.cellSize) * this.#scale;
            const startY = (-this.#offsetY % this.cellSize) * this.#scale;

            // Desenha rótulos dos eixos nas posições das linhas de grade
            for (let x = startX; x <= width; x += step) {
                const source = x;
                const virtualX = this.toVirtualX(source / this.#scale);
                this.context.fillText(`${virtualX.toFixed(0)}`, source, 10);
            }
            for (let y = startY; y <= height; y += step) {
                const destination = y;
                const virtualY = this.toVirtualY(destination / this.#scale);
                this.context.fillText(`${virtualY.toFixed(0)}`, 0, destination);
            }

            // Desenha somente um traço central entre vértices (formato "+ - +")
            const dashLen = 2; // comprimento do traço em pixels de tela
            this.context.beginPath();

            // Dashes em linhas verticais (pequeno segmento vertical no meio de cada célula)
            for (let x = startX; x <= width; x += step) {
                for (let y = startY; y + step <= height; y += step) {
                    const midY = y + step / 2;
                    this.context.moveTo(x, midY - dashLen / 2);
                    this.context.lineTo(x, midY + dashLen / 2);
                }
            }

            // Dashes em linhas horizontais (pequeno segmento horizontal no meio de cada célula)
            for (let y = startY; y <= height; y += step) {
                for (let x = startX; x + step <= width; x += step) {
                    const midX = x + step / 2;
                    this.context.moveTo(midX - dashLen / 2, y);
                    this.context.lineTo(midX + dashLen / 2, y);
                }
            }
            this.context.stroke();

            // Desenha marcadores de vértice em formato "+"
            const markerColor = "rgb(209,213,219)"; // um pouco mais escuro
            const arm = 5; // tamanho do braço do "+" em pixels de tela
            this.context.strokeStyle = markerColor;
            this.context.lineWidth = 1;
            this.context.beginPath();

            for (let x = startX; x <= width; x += step) {
                for (let y = startY; y <= height; y += step) {
                    // Horizontal do "+"
                    this.context.moveTo(x - arm, y);
                    this.context.lineTo(x + arm, y);
                    // Vertical do "+"
                    this.context.moveTo(x, y - arm);
                    this.context.lineTo(x, y + arm);
                }
            }
            this.context.stroke();
        }
    }
}
