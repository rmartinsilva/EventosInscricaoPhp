Projeto Php e Laravel para Inscrição em Evento

Funcionamento Simplificado do Backend (PHP/Laravel)
O backend em PHP com Laravel funcionará da seguinte maneira para gerenciar o sistema de inscrição em eventos:

1. Cadastro de Eventos (Admin):

Um administrador acessa uma área protegida do sistema (painel administrativo).
Através de uma interface, o administrador envia os dados de um novo evento para o backend.
O backend recebe esses dados, verifica se estão corretos e os salva no banco de dados.
2. Cadastro de Usuários (Participantes):

Quando um usuário acessa a página de inscrição de um evento, ele preenche um formulário com seus dados.
Ao enviar o formulário (ou ao digitar o CPF para busca), o frontend envia esses dados para o backend.
O backend verifica se o CPF já existe no banco de dados.
Se existir: O backend retorna os dados do usuário para o frontend.
Se não existir: O backend salva os novos dados do usuário no banco de dados.
3. Realização da Inscrição:

Após o cadastro (ou busca), o frontend envia para o backend a solicitação de inscrição do usuário no evento específico.
O backend registra essa inscrição no banco de dados, associando o usuário ao evento.
O backend então inicia a comunicação com o Mercado Pago para criar uma "preferência de pagamento" para essa inscrição. Essa preferência contém informações sobre o evento e o valor a ser pago.
O backend retorna ao frontend uma URL (ou outras informações necessárias) para que o usuário seja redirecionado para a página de pagamento do Mercado Pago dentro do próprio sistema.
4. Processamento de Pagamento (Mercado Pago):

O usuário interage com a interface de pagamento do Mercado Pago, inserindo seus dados de pagamento (cartão, boleto, etc.).
O Mercado Pago processa o pagamento de forma segura.
Após o sucesso ou falha do pagamento, o Mercado Pago notifica o backend através de um sistema de "webhooks" (notificações automáticas).
O backend recebe essa notificação, verifica o status do pagamento e atualiza o registro de inscrição no banco de dados (confirmando o pagamento ou indicando falha).
5. Login de Administradores:

Um administrador insere suas credenciais (usuário e senha) em uma tela de login.
O backend recebe essas credenciais, verifica se correspondem a um usuário administrador no banco de dados.
Se as credenciais forem válidas, o backend gera um token JWT (JSON Web Token).
Este token é enviado de volta para o frontend do painel administrativo, que o utilizará para provar sua identidade em futuras requisições ao backend (para cadastrar eventos, gerar relatórios, etc.).
6. Autenticação de Administradores:

Quando o frontend do painel administrativo faz uma requisição para uma área protegida (ex: cadastrar evento, gerar relatório), ele envia o token JWT no cabeçalho da requisição.
O backend recebe a requisição, verifica a validade do token JWT.
Se o token for válido e pertencer a um usuário com permissão de administrador, o backend processa a requisição. Caso contrário, a requisição é negada.
7. Relatórios:

Quando um administrador solicita um relatório (de eventos ou participantes), o frontend do painel administrativo envia essa solicitação para o backend (com o token JWT de autenticação).
O backend consulta o banco de dados para obter as informações necessárias (lista de eventos, lista de participantes por evento).
O backend formata esses dados e os envia de volta para o frontend para serem exibidos.
8. Inscrição de Cortesia:

Um administrador, através do painel administrativo, pode solicitar a criação de uma inscrição de cortesia para um determinado usuário em um evento específico.
O backend recebe essa solicitação, verifica se o administrador tem permissão para realizar essa ação e cria um registro de inscrição no banco de dados para o usuário no evento, marcando-o como cortesia (sem passar pelo fluxo de pagamento do Mercado Pago).
Em resumo, o backend atua como o cérebro da aplicação, gerenciando os dados, a lógica de negócios, a segurança e a comunicação com o banco de dados e serviços externos como o Mercado Pago, enquanto o frontend se concentra na apresentação das informações e na interação com o usuário.

## Padrões de Controller

### Método `update` em Controllers da API REST

Para manter a consistência e clareza no tratamento de atualizações de recursos via API, adote o seguinte padrão nos métodos `update` dos controllers (localizados em `app/Http/Controllers/Api/...`):

1.  **Verificar Existência:** Antes de tentar a atualização, verifique se o recurso com o `id` fornecido realmente existe no banco de dados utilizando o método `findOne()` do serviço correspondente.
2.  **Retornar 404:** Se o `findOne()` retornar `null` ou `false`, retorne imediatamente uma resposta JSON com status `404 Not Found` e uma mensagem de erro apropriada (ex: `{"error": "Recurso não encontrado!"}`).
3.  **Prosseguir com Atualização:** Se o recurso for encontrado, crie o DTO de atualização (ex: `UpdateXxxDTO`) a partir da `request` e chame o método `update()` do serviço.
4.  **Verificar Falha na Atualização:** Verifique o retorno do método `update()` do serviço. Se ele indicar falha (retornando `null`, `false`, ou lançando uma exceção específica que você captura), retorne uma resposta JSON com status `500 Internal Server Error` ou outro código apropriado que indique uma falha durante o processo de atualização (ex: `{"error": "Erro ao atualizar recurso!"}`).
5.  **Retornar Sucesso:** Se a atualização for bem-sucedida, utilize a `Resource` apropriada (ex: `XxxObjectResource` ou `XxxResource`) para formatar o recurso atualizado retornado pelo serviço e retorne-o no corpo da resposta JSON com status `200 OK`.

**Exemplo (simplificado):**

```php
public function update(UpdateResourceRequest $request, string $id)
{
    // 1. Verifica existência
    if (!$resource = $this->service->findOne($id)) {
        return response()->json(['error' => 'Recurso não encontrado!'], Response::HTTP_NOT_FOUND);
    }

    // 2. Tenta atualizar
    $dto = UpdateResourceDTO::makeFromRequest($request, $id);
    $updatedResource = $this->service->update($dto);

    // 3. Verifica falha
    if (!$updatedResource) {
        return response()->json(['error' => 'Erro ao atualizar recurso!'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    // 4. Retorna sucesso
    $resourceResponse = new ResourceObject($updatedResource); // Usar a Resource correta
    return $resourceResponse->toObject(); // Ou ->toJson(), dependendo do seu padrão
}
```

Este padrão já foi aplicado nos seguintes controllers:

*   `app/Http/Controllers/Api/Painel/UsuarioController.php`
*   `app/Http/Controllers/Api/Painel/GrupoController.php`
*   `app/Http/Controllers/Api/Painel/AcessoController.php`

Manter este padrão ajudará a garantir que os endpoints de atualização da API tenham um comportamento previsível e consistente.