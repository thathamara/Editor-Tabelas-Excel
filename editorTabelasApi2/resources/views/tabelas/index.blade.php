<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"> <!-- Proteção CSRF -->
    <title>Editor de Tabelas</title>
    
    <!-- CSS -->
    @vite(['resources/css/style.css']) <!-- Se usar Vite -->
    <!-- OU -->
    <!-- <link href="{{ asset('assets/style.css') }}" rel="stylesheet"> Se não usar Vite -->

    <!-- Bibliotecas JS via CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/papaparse@5.3.2/papaparse.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>Editor de Tabelas</h1>
        
        <div class="upload-section">
            <!-- Dropdown para tabelas salvas -->
            <div class="saved-tables-section">
                <label for="saved-tables">Tabelas Salvas:</label>
                <select id="saved-tables">
                    <option value="">-- Selecione uma tabela --</option>
                    @foreach($tabelas as $tabela)
                        <option value="{{ $tabela->id }}">{{ $tabela->name }}</option>
                    @endforeach
                </select>
                <button id="load-saved-btn">Carregar Tabela</button>
                <button id="delete-table-btn" class="danger">Excluir Tabela</button>
            </div>
            
            <hr>
            
    <!-- Upload de novas tabelas -->
    <h3>Ou carregue uma nova tabela:</h3>
    <input type="file" id="file-input" accept=".xlsx, .xls, .csv">
    <button id="load-btn">Carregar Arquivo</button>
    <button id="save-table-btn">Salvar Tabela</button>
    
    <!-- Seletor de planilhas (inicialmente oculto) -->
        <div id="modals-container"></div>
            
            <div id="image-warning" class="alert hidden">
                ⚠️ Este visualizador não suporta imagens do Excel.
            </div>
        </div>
        
        <div id="table-container" class="hidden">
            <div id="table-toolbar">
                <button id="add-row-btn">Adicionar Linha</button>
                <button id="export-pdf-btn">Exportar PDF</button>
                <button id="export-excel-btn">Exportar Excel</button>
            </div>
            <div id="table-wrapper"></div>
        </div>
        
        <div id="loading" class="hidden">
            <div class="spinner"></div>
            <p>Processando...</p>
        </div>

        
        <!-- Modal para salvar tabela -->
        <div id="save-modal" class="modal hidden">
            <div class="modal-content">
                <span id="close-save-modal" class="close">&times;</span>
                <h3>Salvar Tabela</h3>
                <input type="text" id="table-name" placeholder="Digite um nome para a tabela">
                <button id="confirm-save-btn">Salvar</button>
            </div>
        </div>
    </div>


    <!-- JavaScript -->
    @vite(['resources/js/script.js']) <!-- Se usar Vite -->
    <!-- OU -->
    <!-- <script src="{{ asset('assets/script.js') }}"></script> -->
</body>
</html>