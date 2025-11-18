import * as d3 from 'd3';

export default function foreignKeysGraph(relationships, currentTableId, currentTableName, tablesColors) {
    return {
        relationships: relationships || [],
        currentTableId: currentTableId,
        currentTableName: currentTableName || `Table ${currentTableId}`,
        tablesColors: tablesColors || {},
        svg: null,
        simulation: null,
        width: 0,
        height: 0,

        init() {
            this.$nextTick(() => {
                const setupWhenVisible = () => {
                    if (!this.$el) return false;

                    const tabPanel = this.$el.closest('#tab-foreign-keys');
                    const isVisible = tabPanel && !tabPanel.classList.contains('hidden') && this.$el.offsetParent !== null;

                    if (isVisible && !this.svg && this.$el.clientWidth > 0 && this.$el.clientHeight > 0) {
                        this.setupGraph();
                        return true;
                    }
                    return false;
                };

                // Tentar configurar imediatamente
                if (!setupWhenVisible()) {
                    // Se não funcionou, tentar novamente após um delay
                    setTimeout(() => {
                        if (!this.svg) {
                            setupWhenVisible();
                        }
                    }, 300);
                }

                // Observar mudanças na tab
                const tabPanel = this.$el?.closest('#tab-foreign-keys');
                if (tabPanel) {
                    const mutationObserver = new MutationObserver(() => {
                        if (!this.svg) {
                            setTimeout(() => setupWhenVisible(), 200);
                        }
                    });

                    mutationObserver.observe(tabPanel, {
                        attributes: true,
                        attributeFilter: ['class']
                    });
                }

                // Usar IntersectionObserver como fallback
                if (this.$el && 'IntersectionObserver' in window) {
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting && !this.svg) {
                                setTimeout(() => setupWhenVisible(), 200);
                            }
                        });
                    }, {
                        threshold: 0.1
                    });

                    observer.observe(this.$el);
                }
            });
        },

        setupGraph() {
            if (!this.$el) return;

            // Parar simulação anterior se existir
            if (this.simulation) {
                this.simulation.stop();
                this.simulation = null;
            }

            // Limpar SVG anterior se existir
            d3.select(this.$el).selectAll("*").remove();
            this.svg = null;

            // Verificar se há relacionamentos antes de renderizar
            if (!this.relationships || this.relationships.length === 0) {
                // Mostrar mensagem de que não há relacionamentos
                const container = this.$el;
                this.width = container.clientWidth;
                this.height = container.clientHeight;

                if (this.width > 0 && this.height > 0) {
                    const svg = d3.select(container)
                        .append("svg")
                        .attr("width", this.width)
                        .attr("height", this.height);

                    svg.append("text")
                        .attr("x", this.width / 2)
                        .attr("y", this.height / 2)
                        .attr("text-anchor", "middle")
                        .attr("fill", "#6b7280")
                        .attr("font-size", "14px")
                        .text("Nenhuma foreign key encontrada");

                    this.svg = svg;
                }
                return;
            }

            // Verificar se já está renderizado e visível
            const tabPanel = this.$el.closest('#tab-foreign-keys');
            if (tabPanel && tabPanel.classList.contains('hidden')) {
                return; // Não renderizar se a tab estiver escondida
            }

            const container = this.$el;
            this.width = container.clientWidth;
            this.height = container.clientHeight;

            if (this.width === 0 || this.height === 0) {
                // Tentar novamente após um delay
                setTimeout(() => {
                    if (this.$el && this.$el.clientWidth > 0 && this.$el.clientHeight > 0) {
                        this.setupGraph();
                    }
                }, 200);
                return;
            }

            // Criar SVG
            this.svg = d3.select(container)
                .append("svg")
                .attr("width", this.width)
                .attr("height", this.height);

            // Adicionar grupo para zoom
            const g = this.svg.append("g");

            // Configurar zoom
            const zoom = d3.zoom()
                .scaleExtent([0.1, 3])
                .on("zoom", (event) => {
                    g.attr("transform", event.transform);
                });

            this.svg.call(zoom);

            // Preparar dados
            const nodes = this.buildNodes();
            const links = this.buildLinks(nodes);

            // Se não há nós ou apenas um nó sem links, mostrar mensagem
            if (nodes.length === 0 || (nodes.length === 1 && links.length === 0)) {
                g.append("text")
                    .attr("x", this.width / 2)
                    .attr("y", this.height / 2)
                    .attr("text-anchor", "middle")
                    .attr("fill", "#6b7280")
                    .attr("font-size", "14px")
                    .text("Nenhuma foreign key encontrada");
                return;
            }

            // Criar força
            this.simulation = d3.forceSimulation(nodes)
                .force("link", d3.forceLink(links).id(d => d.id).distance(100))
                .force("charge", d3.forceManyBody().strength(-300))
                .force("center", d3.forceCenter(this.width / 2, this.height / 2))
                .force("collision", d3.forceCollide().radius(50));

            // Detectar dark mode
            const isDark = document.documentElement.classList.contains('dark');
            const linkColor = isDark ? '#64748b' : '#94a3b8';
            const textColor = isDark ? '#e5e7eb' : '#1f2937';

            // Função para obter cor da tabela
            const getTableColor = (tableId) => {
                return this.tablesColors[tableId] || '#2563eb';
            };

            // Criar links
            const link = g.append("g")
                .selectAll("line")
                .data(links)
                .enter()
                .append("line")
                .attr("stroke", linkColor)
                .attr("stroke-width", 2)
                .attr("stroke-opacity", 0.6)
                .attr("marker-end", "url(#arrowhead)");

            // Criar marcador de seta
            this.svg.append("defs")
                .append("marker")
                .attr("id", "arrowhead")
                .attr("viewBox", "0 -5 10 10")
                .attr("refX", 25)
                .attr("refY", 0)
                .attr("markerWidth", 6)
                .attr("markerHeight", 6)
                .attr("orient", "auto")
                .append("path")
                .attr("d", "M0,-5L10,0L0,5")
                .attr("fill", linkColor);

            // Criar nós
            const node = g.append("g")
                .selectAll("g")
                .data(nodes)
                .enter()
                .append("g")
                .attr("class", "node")
                .call(this.drag(this.simulation));

            // Círculo do nó
            node.append("circle")
                .attr("r", 20)
                .attr("fill", d => getTableColor(d.id))
                .attr("stroke", isDark ? "#1e293b" : "#fff")
                .attr("stroke-width", d => d.id === this.currentTableId ? 3 : 2)
                .attr("opacity", d => d.id === this.currentTableId ? 1 : 0.9);

            // Label do nó
            node.append("text")
                .attr("dy", 35)
                .attr("text-anchor", "middle")
                .attr("fill", textColor)
                .attr("font-size", "11px")
                .attr("font-weight", "500")
                .text(d => d.name);

            // Atualizar posições
            this.simulation.on("tick", () => {
                link
                    .attr("x1", d => d.source.x)
                    .attr("y1", d => d.source.y)
                    .attr("x2", d => d.target.x)
                    .attr("y2", d => d.target.y);

                node.attr("transform", d => `translate(${d.x},${d.y})`);
            });
        },

        buildNodes() {
            const nodeMap = new Map();

            // Adicionar tabela atual primeiro
            if (this.currentTableId) {
                nodeMap.set(this.currentTableId, {
                    id: this.currentTableId,
                    name: this.currentTableName
                });
            }

            // Adicionar outras tabelas das relações
            this.relationships.forEach(rel => {
                if (rel.from_table_id && rel.from_table && !nodeMap.has(rel.from_table_id)) {
                    nodeMap.set(rel.from_table_id, {
                        id: rel.from_table_id,
                        name: rel.from_table.name || `Table ${rel.from_table_id}`
                    });
                }
                if (rel.to_table_id && rel.to_table && !nodeMap.has(rel.to_table_id)) {
                    nodeMap.set(rel.to_table_id, {
                        id: rel.to_table_id,
                        name: rel.to_table.name || `Table ${rel.to_table_id}`
                    });
                }
            });

            return Array.from(nodeMap.values());
        },

        buildLinks(nodes) {
            const nodeMap = new Map(nodes.map(n => [n.id, n]));
            const links = [];

            this.relationships.forEach(rel => {
                const source = nodeMap.get(rel.from_table_id);
                const target = nodeMap.get(rel.to_table_id);

                if (source && target) {
                    const fromColName = rel.from_column?.name || '';
                    const toColName = rel.to_column?.name || '';
                    const label = rel.label || (fromColName && toColName ? `${fromColName} → ${toColName}` : '');

                    links.push({
                        source: source,
                        target: target,
                        type: rel.type || '1:n',
                        label: label
                    });
                }
            });

            return links;
        },

        drag(simulation) {
            function dragstarted(event) {
                if (!event.active) simulation.alphaTarget(0.3).restart();
                event.subject.fx = event.subject.x;
                event.subject.fy = event.subject.y;
            }

            function dragged(event) {
                event.subject.fx = event.x;
                event.subject.fy = event.y;
            }

            function dragended(event) {
                if (!event.active) simulation.alphaTarget(0);
                event.subject.fx = null;
                event.subject.fy = null;
            }

            return d3.drag()
                .on("start", dragstarted)
                .on("drag", dragged)
                .on("end", dragended);
        }
    };
}

