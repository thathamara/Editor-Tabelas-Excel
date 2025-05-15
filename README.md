# 📊 Sistema Editor de Tabelas

Sistema desenvolvido em Laravel 10 com o objetivo de importar, editar e exportar arquivos no formato `.xls` para `.mdf`. A funcionalidade de importação é exclusiva para administradores, enquanto usuários comuns podem acessar e exportar os dados.

---

## ✅ Funcionalidades Atuais

* ✅ Importa arquivos `.xls`
* ✅ Exporta arquivos `.xls` para `.mdf`
* ✅ Upload de arquivos XML (ainda não reconhece anexos)
* ✅ Interface com DDL para seleção de tabelas salvas (implementado, mas ainda com erros)
* ✅ Salvamento no banco de dados (implementado, mas ainda com erro)

---

## 🛠️ A Fazer

* [ ] Separar telas de Administrador e Usuário Comum
* [ ] Implementar sistema de login
* [ ] Corrigir erro ao salvar tabela: `state is not defined`

---

## ⚙️ Requisitos

* PHP >= 8.0
* Composer
* Laravel 10
* SQL Server
* XAMPP (com Apache e driver `SQLSRV` instalado)

---

## 🚀 Instalação do Projeto

1. Clone o repositório:

```bash
git clone https://github.com/thathamara/Editor-Tabelas-Excel.git
```

2. Acesse a pasta do projeto:

```bash
cd Editor-Tabelas-Excel
```

3. Instale as dependências:

```bash
composer install
```

4. Copie o arquivo de ambiente:

```bash
cp .env.example .env
```

5. Configure o banco de dados no `.env`:

```
DB_CONNECTION=sqlsrv
DB_HOST=127.0.0.1
DB_PORT=1433
DB_DATABASE=Editor
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

6. Gere a chave da aplicação:

```bash
php artisan key:generate
```

7. Rode as migrations (se existirem):

```bash
php artisan migrate
```

8. Inicie o servidor local:

```bash
php artisan serve
```

> Acesse: [http://localhost:8000/tabelas](http://localhost:8000/tabelas)

---

## 🗂️ Estrutura do Projeto

| Caminho                                   | Função                                    |
| ----------------------------------------- | ----------------------------------------- |
| `app/Models`                              | Modelos Eloquent das tabelas              |
| `database/migrations`                     | Estrutura das tabelas do banco de dados   |
| `app/Http/Controllers`                    | Lógica dos controladores                  |
| `resources/views/tabelas/index.blade.php` | Tela principal para exibir/editar tabelas |
| `resources/js/script.js`                  | Scripts JavaScript da aplicação           |
| `resources/css/style.css`                 | Estilização da interface                  |
| `routes/web.php`                          | Rotas Web                                 |
| `routes/api.php`                          | Rotas da API                              |

---

## 📌 Observações

* A funcionalidade de salvamento no banco e escolha de tabelas ainda está em desenvolvimento.
* O sistema foi testado em ambiente local com XAMPP.
* O erro `"state is not defined"` será corrigido nas próximas versões.

---

## 🤝 Contribuição

Caso queira contribuir, abra um *Pull Request* ou envie sugestões via *Issues*.

---

## 🐛 Reporte de Bugs

Se encontrar algum erro ou comportamento inesperado, por favor abra uma [issue no GitHub](https://github.com/thathamara/Editor-Tabelas-Excel/issues).

---

## 👩‍💻 Autora

**Thamara Leany Machado**

📧 [leany.tata@gmail.com](mailto:leany.tata@gmail.com)

---

URL local:http://localhost:8000/tabelas