<x-layouts.landing-page title="Política de Privacidade - CraftDB">
    <div class="pt-24 pb-16">
        <div class="container mx-auto px-6">
            <div class="max-w-3xl mx-auto">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">
                    Política de Privacidade
                </h1>
                <p class="text-gray-500 text-sm mb-12">
                    Última atualização: {{ now()->translatedFormat('d \d\e F \d\e Y') }}
                </p>

                <div class="prose prose-gray max-w-none space-y-10 text-gray-600">
                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">1. Introdução</h2>
                        <p class="leading-relaxed">
                            Esta Política de Privacidade descreve como o CraftDB ("nós", "nosso" ou "Serviço") coleta, usa, armazena e protege suas informações quando você utiliza nossa plataforma.
                            Ao usar o CraftDB, você concorda com as práticas descritas nesta política.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">2. Dados que coletamos</h2>
                        <p class="leading-relaxed mb-4">
                            Podemos coletar:
                        </p>
                        <ul class="list-disc pl-6 space-y-2">
                            <li><strong>Dados de cadastro:</strong> nome, endereço de e-mail e senha (ou dados fornecidos via provedor de login, como Google ou GitHub);</li>
                            <li><strong>Dados de uso:</strong> forma de uso do Serviço, ações no aplicativo e logs técnicos (endereço IP, tipo de navegador, horários de acesso);</li>
                            <li><strong>Conteúdo que você cria:</strong> diagramas, tabelas, relacionamentos e demais dados que você adiciona ou importa no CraftDB.</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">3. Como usamos seus dados</h2>
                        <p class="leading-relaxed mb-4">
                            Utilizamos as informações coletadas para:
                        </p>
                        <ul class="list-disc pl-6 space-y-2">
                            <li>Fornecer, operar e melhorar o Serviço;</li>
                            <li>Autenticar sua conta e gerenciar times e permissões;</li>
                            <li>Enviar comunicações relacionadas à conta (ex.: confirmação de e-mail, recuperação de senha);</li>
                            <li>Responder a suporte e solicitações;</li>
                            <li>Cumprir obrigações legais e proteger direitos e segurança do Serviço e dos usuários.</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">4. Compartilhamento de dados</h2>
                        <p class="leading-relaxed">
                            Não vendemos seus dados pessoais. Podemos compartilhar informações com prestadores de serviços que nos auxiliam na operação do CraftDB (hospedagem, e-mail, analytics),
                            sujeitos a obrigações de confidencialidade. Também podemos divulgar dados quando exigido por lei ou para proteger direitos e segurança.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">5. Retenção e segurança</h2>
                        <p class="leading-relaxed">
                            Mantemos seus dados pelo tempo necessário para fornecer o Serviço e cumprir obrigações legais. Aplicamos medidas técnicas e organizacionais para proteger suas informações contra acesso não autorizado, alteração ou destruição.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">6. Seus direitos</h2>
                        <p class="leading-relaxed">
                            Dependendo da legislação aplicável, você pode ter direito a acessar, corrigir, excluir ou portar seus dados pessoais, ou a restringir/objetar a determinados tratamentos.
                            Para exercer esses direitos ou esclarecer dúvidas, entre em contato conosco através dos canais indicados no site.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">7. Cookies e tecnologias similares</h2>
                        <p class="leading-relaxed">
                            Utilizamos cookies e tecnologias similares para sessão, preferências e melhorar a experiência no Serviço. Você pode configurar seu navegador para recusar cookies; parte das funcionalidades pode ficar limitada.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">8. Alterações nesta política</h2>
                        <p class="leading-relaxed">
                            Podemos atualizar esta Política de Privacidade periodicamente. Alterações relevantes serão comunicadas por e-mail ou aviso no Serviço. A data da "Última atualização" no topo desta página indica a versão vigente.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">9. Contato</h2>
                        <p class="leading-relaxed">
                            Para dúvidas sobre esta Política de Privacidade ou sobre o tratamento dos seus dados, entre em contato através da página de contato ou do suporte disponível no site.
                        </p>
                    </section>
                </div>

                <div class="mt-12 pt-8 border-t border-gray-200">
                    <a href="{{ route('landing.index') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium transition-colors">
                        ← Voltar ao início
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.landing-page>
