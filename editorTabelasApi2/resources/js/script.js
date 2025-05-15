window.state = window.state || {
    tableData: [],
    headers: [],
    currentPage: 0,
    rowsPerPage: 50,
    pdfConfig: {
        orientation: 'landscape',
        unit: 'mm',
        format: 'a3',
        margins: { top: 10, left: 10, right: 10, bottom: 10 }
    }
};

document.addEventListener('DOMContentLoaded', function() {
    // Configurações globais
    const AppConfig = {
        API_URL: '/api/excel-tables',
        CSRF_TOKEN: document.querySelector('meta[name="csrf-token"]').content
    };

    // Estado global completo
    window.state = {
        tableData: [],
        headers: [],
        currentPage: 0,
        rowsPerPage: 50,
        pdfConfig: {
            orientation: 'landscape',
            unit: 'mm',
            format: 'a3',
            margins: { top: 10, left: 10, right: 10, bottom: 10 }
        }
    };

    // Elementos do DOM organizados
    const elements = {
        fileInput: document.getElementById('file-input'),
        loadBtn: document.getElementById('load-btn'),
        tableWrapper: document.getElementById('table-wrapper'),
        tableContainer: document.getElementById('table-container'),
        addRowBtn: document.getElementById('add-row-btn'),
        exportPdfBtn: document.getElementById('export-pdf-btn'),
        exportExcelBtn: document.getElementById('export-excel-btn'),
        loading: document.getElementById('loading'),
        savedTablesSelect: document.getElementById('saved-tables'),
        loadSavedBtn: document.getElementById('load-saved-btn'),
        saveTableBtn: document.getElementById('save-table-btn'),
        deleteTableBtn: document.getElementById('delete-table-btn'),
        saveModal: document.getElementById('save-modal'),
        confirmSaveBtn: document.getElementById('confirm-save-btn'),
        tableNameInput: document.getElementById('table-name'),
        imageWarning: document.getElementById('image-warning')
    };

    // Inicialização
    loadSavedTables();

    // --- Funções Principais ---

    async function loadSavedTables() {
        try {
            const response = await fetch(AppConfig.API_URL, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': AppConfig.CSRF_TOKEN
                }
            });
            
            if (!response.ok) throw new Error(await response.text());
            
            const tables = await response.json();
            updateSavedTablesDropdown(tables);
        } catch (error) {
            showError('Erro ao carregar tabelas', error);
        }
    }

    async function saveTable(tableName) {
        try {
            const response = await fetch(AppConfig.API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': AppConfig.CSRF_TOKEN
                },
                body: JSON.stringify({
                    name: tableName,
                    headers: window.state.headers,
                    data: window.state.tableData
                })
            });

            if (!response.ok) throw new Error(await response.text());
            
            return await response.json();
        } catch (error) {
            showError('Erro ao salvar tabela', error);
            throw error;
        }
    }

    async function loadTable(tableId) {
        try {
            const response = await fetch(`${AppConfig.API_URL}/${tableId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': AppConfig.CSRF_TOKEN
                }
            });
            
            if (!response.ok) throw new Error('Tabela não encontrada');
            
            const table = await response.json();
            
            if (!table.headers || !table.data) {
                throw new Error('Formato de tabela inválido');
            }
            
            window.state.headers = table.headers;
            window.state.tableData = table.data;
            window.state.currentPage = 0;
            
            renderPaginatedTable();
            elements.tableContainer.classList.remove('hidden');
            
        } catch (error) {
            throw error;
        }
    }

    // --- Event Listeners ---

    elements.loadSavedBtn.addEventListener('click', async function() {
        const tableId = elements.savedTablesSelect.value;
        if (!tableId) {
            alert('Por favor, selecione uma tabela.');
            return;
        }
        
        showLoading(true);
        
        try {
            await loadTable(tableId);
        } catch (error) {
            console.error('Erro ao carregar tabela:', error);
            alert(error.message);
        } finally {
            showLoading(false);
        }
    });

    elements.saveTableBtn.addEventListener('click', function() {
        if (!window.state.tableData || window.state.tableData.length === 0) {
            alert('Não há dados para salvar. Carregue uma tabela primeiro.');
            return;
        }
        
        if (!window.state.headers || window.state.headers.length === 0) {
            alert('Cabeçalhos não definidos. Carregue um arquivo válido primeiro.');
            return;
        }
        
        elements.saveModal.classList.remove('hidden');
        elements.tableNameInput.value = '';
        setTimeout(() => elements.tableNameInput.focus(), 100);
    });

    elements.confirmSaveBtn.addEventListener('click', async function() {
        const tableName = elements.tableNameInput.value.trim();
        if (!tableName) {
            alert('Por favor, digite um nome para a tabela.');
            return;
        }
        
        showLoading(true);
        
        try {
            const savedTable = await saveTable(tableName);
            await loadSavedTables();
            elements.savedTablesSelect.value = savedTable.id;
            elements.saveModal.classList.add('hidden');
            elements.tableNameInput.value = '';
            alert('Tabela salva com sucesso!');
        } catch (error) {
            console.error('Erro ao salvar tabela:', error);
            alert(`Erro ao salvar tabela: ${error.message}`);
        } finally {
            showLoading(false);
        }
    });

    elements.deleteTableBtn.addEventListener('click', async function() {
        const tableId = elements.savedTablesSelect.value;
        if (!tableId) {
            alert('Por favor, selecione uma tabela para excluir.');
            return;
        }
        
        if (!confirm('Tem certeza que deseja excluir esta tabela?')) {
            return;
        }
        
        showLoading(true);
        
        try {
            const response = await fetch(`${AppConfig.API_URL}/${tableId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': AppConfig.CSRF_TOKEN
                }
            });
            
            if (!response.ok) throw new Error('Erro ao excluir tabela');
            
            await loadSavedTables();
            alert('Tabela excluída com sucesso!');
        } catch (error) {
            console.error('Erro ao excluir tabela:', error);
            alert(`Erro ao excluir tabela: ${error.message}`);
        } finally {
            showLoading(false);
        }
    });

    elements.addRowBtn.addEventListener('click', function() {
        addNewRow();
    });

    elements.loadBtn.addEventListener('click', async function() {
        if (!elements.fileInput.files.length) {
            alert('Por favor, selecione um arquivo.');
            return;
        }
        
        showLoading(true);
        elements.tableWrapper.innerHTML = '';
        
        try {
            const file = elements.fileInput.files[0];
            
            if (file.size > 10 * 1024 * 1024) {
                throw new Error('Arquivo muito grande. Tamanho máximo: 10MB');
            }
            
            if (file.name.endsWith('.csv')) {
                await processCSV(file);
            } else {
                await processExcel(file);
            }
            
            renderPaginatedTable();
            elements.tableContainer.classList.remove('hidden');
            
        } catch (error) {
            console.error('Erro ao processar arquivo:', error);
            alert(`Erro: ${error.message}`);
        } finally {
            showLoading(false);
        }
    });

    elements.exportPdfBtn.addEventListener('click', function() {
        if (window.state.tableData.length === 0) return;
        
        showLoading(true);
        
        setTimeout(() => {
            try {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF(window.state.pdfConfig);
                
                const options = {
                    startY: 20,
                    head: [window.state.headers],
                    body: window.state.tableData,
                    styles: { 
                        fontSize: 8,
                        cellPadding: 2,
                        overflow: 'linebreak',
                        valign: 'middle'
                    },
                    headStyles: {
                        fillColor: [41, 128, 185],
                        textColor: 255,
                        fontSize: 9
                    },
                    columnStyles: {},
                    margin: { top: 10 },
                    pageBreak: 'auto',
                    tableWidth: 'auto'
                };
                
                window.state.headers.forEach((_, i) => {
                    options.columnStyles[i] = { cellWidth: 'auto' };
                });
                
                doc.autoTable(options);
                doc.save('tabela_exportada.pdf');
                
            } catch (error) {
                console.error('Erro ao gerar PDF:', error);
                alert('Erro ao gerar PDF. A tabela pode ser muito larga. Tente exportar em partes.');
            } finally {
                showLoading(false);
            }
        }, 100);
    });

    elements.exportExcelBtn.addEventListener('click', function() {
        if (window.state.tableData.length === 0) return;
        
        showLoading(true);
        
        try {
            const wb = XLSX.utils.book_new();
            const exportData = [window.state.headers, ...window.state.tableData.slice(0, 1000000)];
            const ws = XLSX.utils.aoa_to_sheet(exportData);
            
            XLSX.utils.book_append_sheet(wb, ws, "Dados");
            XLSX.writeFile(wb, 'tabela_exportada.xlsx');
            
        } catch (error) {
            console.error('Erro ao exportar Excel:', error);
            alert('Erro ao exportar para Excel. O arquivo pode ser muito grande.');
        } finally {
            showLoading(false);
        }
    });

    // --- Funções Auxiliares ---

    function addNewRow() {
        if (window.state.headers.length === 0) {
            alert('Por favor, carregue um arquivo primeiro.');
            return;
        }
        
        const newRow = window.state.headers.map(() => '');
        window.state.tableData.push(newRow);
        renderPaginatedTable();
        
        setTimeout(() => {
            elements.tableWrapper.scrollTop = elements.tableWrapper.scrollHeight;
        }, 100);
    }

async function processExcel(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            try {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, { 
                    type: 'array',
                    cellHTML: false,
                    cellImages: false,
                    sheetStubs: false
                });
                
                // Se tiver apenas 1 planilha, processa diretamente
                if (workbook.SheetNames.length === 1) {
                    const result = processSheet(workbook.Sheets[workbook.SheetNames[0]]);
                    resolve(result);
                } 
                // Se tiver múltiplas planilhas, mostra o seletor
                else {
                    showSheetSelector(workbook, resolve, reject);
                }
            } catch (error) {
                reject(error);
            }
        };
        
        reader.onerror = () => reject(new Error('Erro ao ler o arquivo'));
        reader.readAsArrayBuffer(file);
    });
}

function showSheetSelector(workbook, resolve, reject) {
    try {
        // Cria o modal de seleção (adicione este HTML ao seu arquivo)
        const modalHTML = `
            <div id="sheet-modal" class="modal">
                <div class="modal-content">
                    <h3>Selecione a planilha</h3>
                    <select id="sheets-select" class="w-full p-2 border rounded">
                        ${workbook.SheetNames.map(name => 
                            `<option value="${name}">${name}</option>`
                        ).join('')}
                    </select>
                    <div class="modal-actions">
                        <button id="confirm-sheet" class="btn-primary">Confirmar</button>
                        <button id="cancel-sheet" class="btn-secondary">Cancelar</button>
                    </div>
                </div>
            </div>
        `;
        
        // Adiciona o modal ao DOM
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Configura os eventos
        document.getElementById('confirm-sheet').addEventListener('click', () => {
            try {
                const selectedSheet = document.getElementById('sheets-select').value;
                const result = processSheet(workbook.Sheets[selectedSheet]);
                document.getElementById('sheet-modal').remove();
                resolve(result);
            } catch (error) {
                reject(error);
            }
        });
        
        document.getElementById('cancel-sheet').addEventListener('click', () => {
            document.getElementById('sheet-modal').remove();
            reject(new Error('Seleção cancelada pelo usuário'));
        });
        
    } catch (error) {
        reject(error);
    }
}

    function processSheet(sheet) {
        const jsonData = XLSX.utils.sheet_to_json(sheet, { 
            header: 1,
            defval: "",
            raw: true
        });
        
        if (jsonData.length > 0) {
            state.headers = jsonData[0];
            state.tableData = jsonData.slice(1);
            state.currentPage = 0;
            
            renderPaginatedTable();
            elements.tableContainer.classList.remove('hidden');
        } else {
            alert('A planilha selecionada está vazia.');
        }
    }

    async function processCSV(file) {
        return new Promise((resolve, reject) => {
            Papa.parse(file, {
                header: true,
                complete: function(results) {
                    if (results.data.length > 0) {
                        window.state.headers = Object.keys(results.data[0]);
                        window.state.tableData = results.data.map(row => Object.values(row));
                        resolve();
                    } else {
                        reject(new Error('O arquivo CSV está vazio'));
                    }
                },
                error: function(error) {
                    reject(error);
                }
            });
        });
    }

    function renderPaginatedTable() {
        const start = window.state.currentPage * window.state.rowsPerPage;
        const end = start + window.state.rowsPerPage;
        const pageData = window.state.tableData.slice(start, end);
        
        let html = `
            <div class="table-controls">
                <button id="prev-page" ${window.state.currentPage === 0 ? 'disabled' : ''}>Anterior</button>
                <span>Página ${window.state.currentPage + 1} de ${Math.ceil(window.state.tableData.length / window.state.rowsPerPage)}</span>
                <button id="next-page" ${end >= window.state.tableData.length ? 'disabled' : ''}>Próxima</button>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>${window.state.headers.map(h => `<th>${h}</th>`).join('')}</tr>
                    </thead>
                    <tbody>
        `;
        
        pageData.forEach((row, rowIndex) => {
            html += '<tr>';
            row.forEach((cell, cellIndex) => {
                html += `
                    <td>
                        <input type="text" value="${cell || ''}" 
                               data-row="${start + rowIndex}" 
                               data-col="${cellIndex}"
                               onchange="updateCell(this)">
                    </td>
                `;
            });
            html += `<td><button class="delete-row" data-row="${start + rowIndex}">×</button></td>`;
            html += '</tr>';
        });
        
        html += `
                    </tbody>
                </table>
            </div>
        `;
        
        elements.tableWrapper.innerHTML = html;
        
        document.getElementById('prev-page')?.addEventListener('click', () => {
            window.state.currentPage--;
            renderPaginatedTable();
        });
        
        document.getElementById('next-page')?.addEventListener('click', () => {
            window.state.currentPage++;
            renderPaginatedTable();
        });
        
        document.querySelectorAll('.delete-row').forEach(btn => {
            btn.addEventListener('click', function() {
                const rowIndex = parseInt(this.getAttribute('data-row'));
                window.state.tableData.splice(rowIndex, 1);
                renderPaginatedTable();
            });
        });
    }

    function showLoading(show) {
        elements.loading.style.display = show ? 'flex' : 'none';
    }

    function showError(context, error) {
        console.error(`${context}:`, error);
        alert(`${context}: ${error.message}`);
    }

    function updateSavedTablesDropdown(tables) {
        elements.savedTablesSelect.innerHTML = '<option value="">-- Selecione uma tabela --</option>';
        
        tables.forEach(table => {
            const option = document.createElement('option');
            option.value = table.id;
            option.textContent = table.name || `Tabela ${table.id}`;
            elements.savedTablesSelect.appendChild(option);
        });
    }

    // Função global para atualizar células
window.updateCell = function(input) {
        if (!window.state) {
            console.error("State não está definido!");
            return;
        }
        
        const row = parseInt(input.getAttribute('data-row'));
        const col = parseInt(input.getAttribute('data-col'));
        
        if (window.state.tableData && window.state.tableData[row]) {
            window.state.tableData[row][col] = input.value;
        }
    };
});

