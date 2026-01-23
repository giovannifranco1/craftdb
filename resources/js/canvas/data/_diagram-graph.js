import * as d3 from 'd3';

export default function diagramGraph(relationships, tablesColors) {
    return {
        relationships: relationships || [],
        tablesColors: tablesColors || {},
        svg: null,
        simulation: null,
        width: 0,
        height: 0,

        init() {
            // Expor setupGraph para ser chamado externamente
            this.setupGraphWrapper = () => {
                if (!this.$el) return;
                this.svg = null; // Reset SVG to force re-render
                this.setupGraph();
            };

            const setupGraph = () => {
                if (!this.$el || this.svg) return;

                // Forçar renderização após um delay para garantir que o modal está visível
                setTimeout(() => {
                    if (this.$el && !this.svg) {
                        this.setupGraph();
                    }
                }, 300);
            };

            // Tentar renderizar imediatamente
            this.$nextTick(() => {
                setupGraph();
            });

            // Observar mudanças no modal
            this.$nextTick(() => {
                const modal = this.$el?.closest('[role="dialog"]');
                if (modal) {
                    const mutationObserver = new MutationObserver(() => {
                        if (!this.svg && this.$el) {
                            const isVisible = modal.getAttribute('aria-hidden') !== 'true' &&
                                           !modal.classList.contains('hidden');
                            if (isVisible) {
                                setTimeout(() => {
                                    if (!this.svg && this.$el) {
                                        this.setupGraph();
                                    }
                                }, 200);
                            }
                        }
                    });

                    mutationObserver.observe(modal, {
                        attributes: true,
                        attributeFilter: ['class', 'aria-hidden', 'style']
                    });
                }
            });

            // Usar IntersectionObserver
            this.$nextTick(() => {
                if (this.$el && 'IntersectionObserver' in window) {
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting && entry.intersectionRatio > 0 && !this.svg) {
                                setTimeout(() => {
                                    if (!this.svg && this.$el) {
                                        this.setupGraph();
                                    }
                                }, 200);
                            }
                        });
                    }, {
                        threshold: 0.1
                    });

                    observer.observe(this.$el);
                }
            });

            // Listener global para quando o modal abre
            const handleModalOpen = (e) => {
                if (e.detail?.name === 'diagram-graph' && !this.svg && this.$el) {
                    setTimeout(() => {
                        if (!this.svg && this.$el) {
                            this.setupGraph();
                        }
                    }, 300);
                }
            };

            window.addEventListener('flux:modal:opened', handleModalOpen);

            // Cleanup
            this.$el.addEventListener('alpine:destroyed', () => {
                window.removeEventListener('flux:modal:opened', handleModalOpen);
            });
        },

        setupGraph() {
            if (!this.$el || this.svg) return;

            // Verificar se o container tem dimensões válidas
            const container = this.$el;
            this.width = container.clientWidth || container.offsetWidth || 800;
            this.height = container.clientHeight || container.offsetHeight || 600;

            // Se ainda não tem dimensões, usar valores padrão maiores
            if (this.width === 0 || this.height === 0) {
                this.width = 800;
                this.height = 600;
            }

            // Verificar se há relacionamentos antes de renderizar
            if (!this.relationships || this.relationships.length === 0) {
                d3.select(this.$el).selectAll("*").remove();
                this.svg = null;

                const svg = d3.select(container)
                    .append("svg")
                    .attr("width", this.width)
                    .attr("height", this.height)
                    .style("display", "block")
                    .style("margin", "0 auto");

                svg.append("text")
                    .attr("x", this.width / 2)
                    .attr("y", this.height / 2)
                    .attr("text-anchor", "middle")
                    .attr("dominant-baseline", "middle")
                    .attr("fill", "#6b7280")
                    .attr("font-size", "14px")
                    .text("No relationships found in the diagram");

                this.svg = svg;
                return;
            }

            // Parar simulação anterior se existir
            if (this.simulation) {
                this.simulation.stop();
                this.simulation = null;
            }

            // Limpar SVG anterior se existir
            d3.select(this.$el).selectAll("*").remove();
            this.svg = null;

            // Criar SVG
            this.svg = d3.select(container)
                .append("svg")
                .attr("width", this.width)
                .attr("height", this.height)
                .style("display", "block")
                .style("margin", "0 auto");

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
                    .attr("dominant-baseline", "middle")
                    .attr("fill", "#6b7280")
                    .attr("font-size", "14px")
                    .text("No relationships found in the diagram");
                return;
            }

            // Criar força
            this.simulation = d3.forceSimulation(nodes)
                .force("link", d3.forceLink(links).id(d => d.id).distance(80))
                .force("charge", d3.forceManyBody().strength(-200))
                .force("center", d3.forceCenter(this.width / 2, this.height / 2))
                .force("collision", d3.forceCollide().radius(30));

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
                .attr("stroke-width", 1.5)
                .attr("stroke-opacity", 0.5)
                .attr("marker-end", "url(#arrowhead)");

            // Criar marcador de seta
            // refX precisa ser maior que o raio do nó (12) para a seta aparecer fora
            this.svg.append("defs")
                .append("marker")
                .attr("id", "arrowhead")
                .attr("viewBox", "0 -5 10 10")
                .attr("refX", 24)
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

            // Círculo do nó (menor para o gráfico completo)
            node.append("circle")
                .attr("r", 12)
                .attr("fill", d => getTableColor(d.id))
                .attr("stroke", isDark ? "#1e293b" : "#fff")
                .attr("stroke-width", 1.5)
                .attr("opacity", 1);

            // Label do nó (menor também)
            node.append("text")
                .attr("dy", 20)
                .attr("text-anchor", "inter")
                .attr("fill", textColor)
                .attr("font-size", "10px")
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

            // Adicionar todas as tabelas das relações
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
                // Filtrar apenas dependências reais
                const type = rel.type || '1:n';
                const toColumn = rel.to_column;

                // Regras de dependência:
                // - 1:n e n:1: sempre mostrar (dependência de cardinalidade)
                // - 1:1: só mostrar se FK é NOT NULL (dependência técnica)
                // - n:n: sempre mostrar

                const shouldShowDependency = (type === '1:n' || type === 'n:1' || type === 'n:n')
                    || (type === '1:1' && toColumn?.is_notnull === 1);

                if (!shouldShowDependency) {
                    return; // Ignora no grafo de dependências
                }

                // A seta deve apontar da tabela dependente (com FK) para a tabela referenciada
                // from_table é o owner/pai, to_table é a child/filha (tem a FK)
                // Logo: to_table precisa de from_table, então seta vai to_table -> from_table
                const source = nodeMap.get(rel.to_table_id);   // Child com FK (dependente)
                const target = nodeMap.get(rel.from_table_id); // Owner referenciada (necessária)

                if (source && target) {
                    links.push({
                        source: source,
                        target: target,
                        type: type
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

