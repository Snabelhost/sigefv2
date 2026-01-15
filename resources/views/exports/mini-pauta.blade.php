<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Mini Pauta - {{ $turma->name ?? 'Turma' }}</title>
    <style>
        @page { 
            size: A4 portrait; 
            margin: 8mm; 
        }
        
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body { 
            font-family: Arial, sans-serif;
            font-size: 9px;
            background: #fff;
        }
        
        .container {
            width: 100%;
            max-width: 195mm;
            margin: 0 auto;
        }
        
        /* Cabeçalho */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 3mm;
            border-bottom: 1px solid #000;
            padding-bottom: 2mm;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 3mm;
        }
        
        .logo {
            width: 40px;
            height: auto;
        }
        
        .header-text {
            text-align: center;
            font-size: 8px;
            line-height: 1.3;
        }
        
        .header-text .escola {
            font-weight: bold;
            font-size: 9px;
        }
        
        .header-right {
            text-align: right;
            font-size: 8px;
        }
        
        .header-right .aprovado {
            border: 1px solid #000;
            padding: 1mm;
            font-size: 7px;
        }
        
        /* Título */
        .titulo {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            margin: 3mm 0;
            text-transform: uppercase;
        }
        
        /* Info da disciplina/turma */
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2mm;
            font-size: 8px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 1mm;
        }
        
        .info-item label {
            font-weight: bold;
        }
        
        .info-item .value {
            border-bottom: 1px solid #000;
            min-width: 30mm;
            padding: 0 1mm;
        }
        
        /* Tabela principal */
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 1mm;
            text-align: center;
            vertical-align: middle;
        }
        
        th {
            background: #d4a574;
            font-weight: bold;
            font-size: 6px;
        }
        
        .th-group {
            background: #b8956b;
        }
        
        td.nome {
            text-align: left;
            font-size: 7px;
            max-width: 50mm;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        td.categoria {
            font-size: 6px;
            background: #f5e6d3;
        }
        
        .col-num { width: 6mm; }
        .col-categoria { width: 15mm; }
        .col-nome { width: 55mm; }
        .col-genero { width: 5mm; }
        .col-nota { width: 6mm; }
        .col-faltas { width: 6mm; }
        .col-media { width: 8mm; }
        .col-resultado { width: 6mm; }
        .col-obs { width: 12mm; }
        
        tr:nth-child(even) td {
            background: #faf5f0;
        }
        
        /* Rodapé */
        .footer {
            margin-top: 3mm;
            font-size: 7px;
        }
        
        .estatistica {
            border: 1px solid #000;
            padding: 2mm;
            margin-bottom: 2mm;
        }
        
        .estatistica-titulo {
            font-weight: bold;
            text-align: center;
            margin-bottom: 1mm;
        }
        
        .estatistica-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 1mm;
            text-align: center;
            font-size: 6px;
        }
        
        .estatistica-grid div {
            border: 1px solid #000;
            padding: 1mm;
        }
        
        .assinatura-row {
            display: flex;
            justify-content: space-between;
            margin-top: 5mm;
        }
        
        .assinatura {
            text-align: center;
            width: 45%;
        }
        
        .assinatura-linha {
            border-top: 1px solid #000;
            margin-top: 10mm;
            padding-top: 1mm;
            font-size: 7px;
        }
        
        .slogan {
            text-align: center;
            font-style: italic;
            font-size: 7px;
            margin: 3mm 0;
        }
        
        .footer-text {
            text-align: center;
            font-size: 7px;
            margin-top: 2mm;
        }

        @media print {
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Cabeçalho -->
        <div class="header">
            <div class="header-left">
                <img src="/images/logo-pna.png" alt="Logo PNA" class="logo">
                <div class="header-text">
                    <div class="escola">{{ strtoupper($instituicao->name ?? 'ESCOLA PRÁTICA DE POLÍCIA') }}</div>
                    <div>ÁREA DE INSTRUÇÃO E ENSINO</div>
                    <div>DEPARTAMENTO DE FORMAÇÃO</div>
                </div>
            </div>
            <div class="header-right">
                <div class="aprovado">
                    APROVADO, AOS ___/___/{{ date('Y') }}<br>
                    O CHEFE DE DEPARTAMENTO
                </div>
                <div style="margin-top: 2mm;">
                    <strong>{{ $instituicao->director ?? 'CHAMBO ILINGA' }}</strong><br>
                    <small>("SUPERINTENDENTE")</small>
                </div>
            </div>
        </div>
        
        <!-- Título -->
        <div class="titulo">MINI PAUTA DO PROFESSOR</div>
        
        <!-- Info -->
        <div class="info-row">
            <div class="info-item">
                <label>Disciplina:</label>
                <span class="value">{{ $disciplina->name ?? '__________________' }}</span>
            </div>
            <div class="info-item">
                <label>Com. Inter.:</label>
                <span class="value">__________</span>
            </div>
        </div>
        <div class="info-row">
            <div class="info-item">
                <label>Turma:</label>
                <span class="value">{{ $turma->name ?? '__________________' }}</span>
            </div>
            <div class="info-item">
                <label>Turno:</label>
                <span class="value">__________</span>
            </div>
            <div class="info-item">
                <label>Ano Lect.:</label>
                <span class="value">{{ $turma->academicYear->year ?? date('Y') }}/{{ date('Y') + 1 }}</span>
            </div>
        </div>
        
        <!-- Tabela de Notas -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th rowspan="2" class="col-num">Nº</th>
                        <th rowspan="2" class="col-categoria">CATEGORIA</th>
                        <th rowspan="2" class="col-nome">NOME ALUNO(A)</th>
                        <th rowspan="2" class="col-genero">G</th>
                        <th colspan="5" class="th-group">IP FASE - CLASSIFICAÇÃO</th>
                        <th rowspan="2" class="col-faltas">FALTAS</th>
                        <th rowspan="2" class="col-media">M. FINAL</th>
                        <th colspan="4" class="th-group">RESULT. FINAIS</th>
                        <th rowspan="2" class="col-obs">OBS</th>
                    </tr>
                    <tr>
                        <th class="col-nota">N P1</th>
                        <th class="col-nota">N P2</th>
                        <th class="col-nota">N P3</th>
                        <th class="col-nota">N P4</th>
                        <th class="col-nota">N P5</th>
                        <th class="col-resultado">R</th>
                        <th class="col-resultado">T</th>
                        <th class="col-resultado">R.FASE</th>
                        <th class="col-resultado">T.A</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alunos as $index => $aluno)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="categoria">FORMANDO</td>
                        <td class="nome">{{ strtoupper($aluno['nome']) }}</td>
                        <td>{{ $aluno['genero'] ?? 'M' }}</td>
                        <td>{{ $aluno['notas']['p1'] ?? '' }}</td>
                        <td>{{ $aluno['notas']['p2'] ?? '' }}</td>
                        <td>{{ $aluno['notas']['p3'] ?? '' }}</td>
                        <td>{{ $aluno['notas']['p4'] ?? '' }}</td>
                        <td>{{ $aluno['notas']['p5'] ?? '' }}</td>
                        <td>{{ $aluno['faltas'] ?? '' }}</td>
                        <td><strong>{{ $aluno['media'] ?? '' }}</strong></td>
                        <td>{{ $aluno['resultado'] ?? '' }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    @empty
                    @for($i = 1; $i <= 50; $i++)
                    <tr>
                        <td>{{ $i }}</td>
                        <td class="categoria">FORMANDO</td>
                        <td class="nome"></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    @endfor
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Estatística -->
        <div class="footer">
            <div class="estatistica">
                <div class="estatistica-titulo">ESTATÍSTICA</div>
                <div class="estatistica-grid">
                    <div>MATRICULADOS<br>MF: ___ F: ___</div>
                    <div>DESIST<br>MF: ___ F: ___</div>
                    <div>AVAL<br>F: ___</div>
                    <div>APTO<br>MF: ___ F: ___</div>
                    <div>NIAPTO<br>F: ___</div>
                    <div>ASSINATURA DO PROFESSOR</div>
                </div>
            </div>
            
            <div class="slogan">"FORMAÇÃO, PROFISSIONALISMO E APERFEIÇOAMENTO"</div>
            
            <div class="footer-text">
                DEPARTAMENTO DE FORMAÇÃO/ÁREA DE INSTRUÇÃO E ENSINO/EPP/PNA, em Luanda, aos ___/___/{{ date('Y') }}.
            </div>
            
            <div class="assinatura-row">
                <div class="assinatura">
                    <div class="assinatura-linha">O COORDENADOR/CHEFE DE CÁTEDRA</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
