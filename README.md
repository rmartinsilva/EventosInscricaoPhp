Projeto Backend desenvolvido em Php + Laravel para um sistema de inscrição em eventos é possivel inscrições cortesias por evento.

instalar as dependências
composer install

copiar o .env.example para .env

Gerar key Laravel 
php artisan key:generate

Gerar token Jwt 
php artisan jwt:secret

Atualize o .env com seus dados de conexão

Cria o Bando de Dados

php artisan migrate

Cria os registros iniciais do banco de dados

php artisan db:seed

senha do administrador login: admin senha: 102030

Url para o Front-end em Angular 19 https://github.com/rmartinsilva/EventosInscricaoAngular