<x-layouts.landing-page title="Termos de Serviço - CraftDB">
    <div class="pt-24 pb-16">
        <div class="container mx-auto px-6">
            <div class="max-w-3xl mx-auto">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">
                    Termos de Serviço
                </h1>
                <p class="text-gray-500 text-sm mb-12">
                    Última atualização: {{ now()->translatedFormat('d \d\e F \d\e Y') }}
                </p>

                <div class="prose prose-gray max-w-none space-y-10 text-gray-600">
                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">1. Aceitação dos termos</h2>
                        <p class="leading-relaxed">
                            Ao acessar ou usar o CraftDB ("Serviço"), você concorda em cumprir e estar vinculado a estes Termos de Serviço.
                            Se você não concordar com qualquer parte destes termos, não deverá utilizar o Serviço.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">2. Descrição do serviço</h2>
                        <p class="leading-relaxed">
                            O CraftDB é uma plataforma para criação e edição de diagramas de banco de dados de forma visual.
                            O Serviço permite importar schemas, definir tabelas e relacionamentos, trabalhar em times e exportar ou compartilhar diagramas,
                            conforme os recursos disponíveis no momento.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">3. Conta e responsabilidade</h2>
                        <p class="leading-relaxed mb-4">
                            Você é responsável por manter a confidencialidade da sua conta e senha e por todas as atividades realizadas em sua conta.
                            Deve notificar-nos imediatamente sobre qualquer uso não autorizado.
                        </p>
                        <p class="leading-relaxed">
                            Você concorda em fornecer informações verdadeiras e atualizadas no cadastro e em usar o Serviço de acordo com a lei aplicável e com estes termos.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">4. Uso aceitável</h2>
                        <p class="leading-relaxed mb-4">
                            Ao usar o CraftDB, você concorda em não:
                        </p>
                        <ul class="list-disc pl-6 space-y-2">
                            <li>Violar leis aplicáveis ou direitos de terceiros;</li>
                            <li>Utilizar o Serviço para fins ilegais ou não autorizados;</li>
                            <li>Tentar obter acesso não autorizado a sistemas, redes ou dados de terceiros;</li>
                            <li>Interferir ou prejudicar o funcionamento do Serviço ou de outros usuários;</li>
                            <li>Reproduzir, distribuir ou explorar comercialmente o Serviço sem autorização prévia.</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">5. Propriedade intelectual e seus dados</h2>
                        <p class="leading-relaxed">
                            O CraftDB e seu conteúdo (exceto os dados que você envia) são de propriedade do CraftDB ou de seus licenciadores e estão protegidos por leis de propriedade intelectual.
                            Você mantém todos os direitos sobre os diagramas e dados que criar ou importar. Ao usar o Serviço, você nos concede as licenças necessárias para armazenar, processar e exibir esse conteúdo conforme o funcionamento do Serviço.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">6. Limitação de responsabilidade</h2>
                        <p class="leading-relaxed">
                            O Serviço é oferecido "como está". Na máxima extensão permitida pela lei, o CraftDB não se responsabiliza por danos indiretos, incidentais, especiais ou consequenciais decorrentes do uso ou da impossibilidade de uso do Serviço.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">7. Alterações</h2>
                        <p class="leading-relaxed">
                            Podemos alterar estes Termos de Serviço a qualquer momento. Alterações significativas serão comunicadas por e-mail ou por aviso no próprio Serviço.
                            O uso continuado do CraftDB após as alterações constitui aceitação dos novos termos.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">8. Contato</h2>
                        <p class="leading-relaxed">
                            Dúvidas sobre estes Termos de Serviço podem ser enviadas através da página de contato ou do suporte disponível no site.
                        </p>
                    </section>
                </div>

                <div class="mt-12 pt-8 border-t border-gray-200">
                    <a href="{{ route('home') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium transition-colors">
                        ← Voltar ao início
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.landing-page>
