<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Pauta Geral - {{ $turma->name ?? 'Turma' }}</title>
    <style>
        @page { 
            size: A4 landscape; 
            margin: 6mm; 
        }
        
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body { 
            font-family: Arial, sans-serif;
            font-size: 8px;
            background: #fff;
        }
        
        .container {
            width: 100%;
        }
        
        /* Cabeçalho */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2mm;
            border-bottom: 1px solid #000;
            padding-bottom: 2mm;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 3mm;
        }
        
        .logo {
            width: 35px;
            height: auto;
        }
        
        .header-text {
            text-align: center;
            font-size: 7px;
            line-height: 1.2;
        }
        
        .header-text .escola {
            font-weight: bold;
            font-size: 8px;
        }
        
        .header-right {
            text-align: right;
            font-size: 7px;
        }
        
        /* Título */
        .titulo {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            margin: 2mm 0;
            text-transform: uppercase;
        }
        
        /* Info */
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1mm;
            font-size: 7px;
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
            min-width: 25mm;
            padding: 0 1mm;
        }
        
        /* Tabela */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 6px;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 0.5mm;
            text-align: center;
            vertical-align: middle;
        }
        
        th {
            background: #d4a574;
            font-weight: bold;
            font-size: 5px;
        }
        
        .th-disciplina {
            background: #b8956b;
            writing-mode: vertical-lr;
            text-orientation: mixed;
            transform: rotate(180deg);
            min-width: 8mm;
            max-width: 10mm;
            height: 25mm;
            font-size: 5px;
            padding: 1mm;
        }
        
        td.nome {
            text-align: left;
            font-size: 6px;
            max-width: 45mm;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        td.categoria {
            font-size: 5px;
            background: #f5e6d3;
        }
        
        .col-num { width: 5mm; }
        .col-nome { width: 45mm; }
        .col-nota { width: 8mm; }
        .col-media { width: 10mm; font-weight: bold; }
        .col-resultado { width: 12mm; }
        
        tr:nth-child(even) td {
            background: #faf5f0;
        }
        
        .aprovado {
            color: green;
            font-weight: bold;
        }
        
        .reprovado {
            color: red;
            font-weight: bold;
        }
        
        /* Rodapé */
        .footer {
            margin-top: 2mm;
            font-size: 6px;
        }
        
        .assinatura-row {
            display: flex;
            justify-content: space-between;
            margin-top: 5mm;
            padding: 0 20mm;
        }
        
        .assinatura {
            text-align: center;
            width: 30%;
        }
        
        .assinatura-linha {
            border-top: 1px solid #000;
            margin-top: 8mm;
            padding-top: 1mm;
            font-size: 6px;
        }
        
        .slogan {
            text-align: center;
            font-style: italic;
            font-size: 6px;
            margin: 2mm 0;
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
                APROVADO, AOS ___/___/{{ date('Y') }}<br>
                O DIRECTOR DA ESCOLA
            </div>
        </div>
        
        <!-- Título -->
        <div class="titulo">PAUTA GERAL DE CLASSIFICAÇÃO</div>
        
        <!-- Info -->
        <div class="info-row">
            <div class="info-item">
                <label>Turma:</label>
                <span class="value">{{ $turma->name ?? '__________________' }}</span>
            </div>
            <div class="info-item">
                <label>Curso:</label>
                <span class="value">{{ $turma->courseMap->course->name ?? '__________________' }}</span>
            </div>
            <div class="info-item">
                <label>Ano Lect.:</label>
                <span class="value">{{ $turma->academicYear->year ?? date('Y') }}/{{ date('Y') + 1 }}</span>
            </div>
        </div>
        
        <!-- Tabela de Notas -->
        <table>
            <thead>
                <tr>
                    <th class="col-num">Nº</th>
                    <th class="col-nome">NOME DO FORMANDO</th>
                    @foreach($disciplinas as $disciplina)
                    <th class="th-disciplina">{{ strtoupper($disciplina->name) }}</th>
                    @endforeach
                    <th class="col-media">MÉDIA GERAL</th>
                    <th class="col-resultado">RESULTADO</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alunos as $index => $aluno)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="nome">{{ strtoupper($aluno['nome']) }}</td>
                    @foreach($disciplinas as $disciplina)
                    <td>{{ $aluno['medias'][$disciplina->id] ?? '-' }}</td>
                    @endforeach
                    <td class="col-media">{{ $aluno['media_geral'] ?? '-' }}</td>
                    <td class="{{ ($aluno['resultado'] ?? '') == 'Aprovado' ? 'aprovado' : 'reprovado' }}">
                        {{ strtoupper($aluno['resultado'] ?? '-') }}
                    </td>
                </tr>
                @empty
                @for($i = 1; $i <= 50; $i++)
                <tr>
                    <td>{{ $i }}</td>
                    <td class="nome"></td>
                    @foreach($disciplinas as $disciplina)
                    <td></td>
                    @endforeach
                    <td></td>
                    <td></td>
                </tr>
                @endfor
                @endforelse
            </tbody>
        </table>
        
        <!-- Rodapé -->
        <div class="footer">
            <div class="slogan">"FORMAÇÃO, PROFISSIONALISMO E APERFEIÇOAMENTO"</div>
            
            <div class="assinatura-row">
                <div class="assinatura">
                    <div class="assinatura-linha">O COORDENADOR PEDAGÓGICO</div>
                </div>
                <div class="assinatura">
                    <div class="assinatura-linha">O DIRECTOR ADJ. P/ INSTRUÇÃO</div>
                </div>
                <div class="assinatura">
                    <div class="assinatura-linha">O DIRECTOR DA ESCOLA</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
