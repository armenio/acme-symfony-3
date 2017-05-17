# Gerenciador de produtos Acme Symfony 3

Api para gestão de produtos da empresa Acme

#### Tecnologias utilizadas
- Zend Framework 3
- Cake ORM com Módulo de integração para zend framework 3 desenvolvido por mim https://github.com/armenio/zf3-cake-orm
- Autenticação com token JWT usando o Bundle LexikJWTAuthenticationBundle: https://github.com/lexik/LexikJWTAuthenticationBundle
- Composer como gerenciador de dependências php
- Banco de dados MySql

## Instalação do projeto

    $ git clone https://github.com/armenio/acme-symfony-3.git aplicacao
    $ cd aplicacao
    $ composer install

- Não conhece o composer? [Veja aqui](http://getcomposer.org/doc/00-intro.md#introduction) como usá-lo
    - * é possível usar o composer sem instalação com o comando:
     ```bash
     $ /caminho/do/php /caminho/do/composer.phar install
     ```

### O arquivo para criação do banco de dados encontra-se em:
/caminho/da/aplicacao/var/db.sql

### Configuração do acesso ao bando de dados:
A configuração do banco de dados e servidor de e-mail é feita durante a instalação

## Rodando a aplicação
     $ cd /caminho/da/aplicacao
     $ php bin/console server:run