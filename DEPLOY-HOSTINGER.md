# Publicação Na Hostinger Sem SSH

O pacote `cbanglo26-hostinger-pronto.zip` já contém o site compilado, o `.env` de produção, a configuração SMTP e um instalador protegido.

## Publicar

1. No hPanel, abra **Bancos de dados MySQL** e crie um banco vazio.
2. Abra o **Gerenciador de Arquivos**, entre em `public_html`, envie o ZIP e clique em **Extrair**.
3. Abra `COMO-PUBLICAR.txt`, que está dentro do ZIP.
4. Acesse o endereço privado de instalação indicado nesse arquivo.
5. Cole o nome do banco, o usuário e a senha exibidos pelo hPanel.
6. Clique em **Ativar agora**.

O instalador testa a conexão, cria todas as tabelas, inclui as escolas, séries, datas, perguntas e o administrador inicial. Depois ele se desativa automaticamente.

## Primeiro Login

- Endereço: `/admin`
- Usuário: `admin`
- Senha inicial: `cbanglo26##`

No primeiro acesso, abra **Usuários** e troque a senha inicial.

Não é necessário usar SSH, terminal, Composer, Node.js ou phpMyAdmin.
