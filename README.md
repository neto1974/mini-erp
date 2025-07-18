# mini-erp

# Mini ERP - Loja de Vestuário
Este é um sistema ERP para uma loja de vestuário, desenvolvido em PHP com MySQL, que gerencia produtos com variações (cor, tamanho, gênero, tecido), carrinho de compras, pedidos, e cupons de desconto. Inclui validação de CEP via API ViaCEP.

## Pré-requisitos
- Servidor web (ex.: Apache via XAMPP)
- PHP >= 7.4
- MySQL
- Composer (opcional, para PHPMailer)
- Conexão à internet (para CDN do Bootstrap/jQuery e API ViaCEP)

## Estrutura do Projeto
- `views/`: Interface do usuário (index.php, cupons.php)
- `models/`: Lógica de negócio (modelo_produto.php, modelo_pedido.php)
- `controllers/`: controllers para ações (controlador_produto.php, controlador_pedido.php)
- `banco_dados.sql`: Script para criar o banco de dados
- `config.php`: Configuração da conexão com o banco

## Instalação
1. **Clone o Repositório**:
   ```bash
   git clone https://github.com/SEU_USUARIO/mini-erp.git
   cd mini-erp

Configure o Banco de Dados:
    Crie um banco de dados MySQL chamado mini_erp.
    Importe o arquivo banco_dados.sql no phpMyAdmin ou via comando:  
    bash 
    mysql -u root -p mini_erp < banco_dados.sql

Configure a Conexão com o Banco:
    Edite config.php com suas credenciais do MySQL:

Configure o Servidor Web:
    Copie a pasta mini-erp para o diretório do servidor web (ex.: htdocs no XAMPP).
    Certifique-se de que o servidor Apache e MySQL estão rodando.

Instale Dependências (Opcional):
    Para usar o envio de e-mails, instale o PHPMailer via Composer:
    bash
    composer require phpmailer/phpmailer
    Descomente o código de envio de e-mail em models/modelo_pedido.php e configure as credenciais SMTP.

Acesse o Sistema:
    Abra http://localhost/mini-erp/views/index.php no navegador.
    
