# 📋 PLANO DE IMPLEMENTAÇÃO - SIGEF v2
## Sistema de Gestão Formativa (SaaS Multi-Instituição)

---

## 🎯 VISÃO GERAL

O SIGEF é um sistema SaaS de gestão académica e formativa policial, onde:
- **Cada instituição possui gestão autónoma**, porém subordinada ao Super Admin central
- Segue os princípios de **hierarquia institucional**, **separação de responsabilidades**, **rastreabilidade** e **conformidade com normas do ensino policial estatal**

---

## 🏗️ ARQUITETURA TÉCNICA

### Stack Tecnológico
| Componente | Tecnologia |
|------------|-----------|
| **Backend** | Laravel 12.x (PHP 8.3) |
| **Frontend/Admin** | Filament 3.x |
| **Banco de Dados** | MySQL 8.x |
| **Autenticação** | Laravel Breeze + Spatie Permissions |
| **Multi-tenancy** | Tenant por coluna (`institution_id`) |
| **Idioma** | Português (pt_PT) |

### Estrutura de Painéis Filament
```
/admin     → Super Admin (Área de Doutrina e Ensino)
/dpq       → DPQ (Direção de Pessoal e Quadro)
/comando   → Comando Geral
/escola    → Instituição de Ensino
```

---

## 📊 MODELO DE DADOS

### Entidades Principais

#### 1. **Users & Auth**
```
users
├── id
├── name
├── email
├── password
├── institution_id (nullable - Super Admin não tem)
├── avatar
├── phone
├── is_active
└── timestamps

roles (Spatie)
├── super_admin
├── dpq_admin
├── dpq_user
├── comando_admin
├── escola_admin
└── escola_user
```

#### 2. **Instituições**
```
institutions
├── id
├── institution_type_id
├── name
├── acronym (sigla)
├── phone
├── email
├── country
├── province
├── municipality
├── address
├── logo
├── is_active
└── timestamps

institution_types
├── id
├── name
└── description
```

#### 3. **Dados Mestres**
```
provenances (Proveniências)
├── id
├── name
├── acronym
└── timestamps

ranks (Patentes/Postos)
├── id
├── name
├── acronym
├── order (hierarquia)
└── timestamps

academic_years (Anos Lectivos)
├── id
├── year (ex: 2026)
├── name (ex: "2025/2026")
├── start_date
├── end_date
├── is_active
└── timestamps
```

#### 4. **Cursos e Planos**
```
courses (Cursos)
├── id
├── name
├── description
├── duration_months
├── has_phases (boolean - para Curso Básico de Polícia)
└── timestamps

course_maps (Mapa de Cursos)
├── id
├── course_id
├── institution_id
├── academic_year_id
├── organ (órgão)
├── max_students
├── is_active
└── timestamps

course_plans (Planos de Curso)
├── id
├── course_id
├── academic_year_id
├── is_active
└── timestamps

course_phases (Fases do Curso)
├── id
├── course_id
├── name (ex: "Fase Militar", "Fase Policial")
├── order
└── timestamps

subjects (Disciplinas)
├── id
├── name
├── description
├── workload_hours
├── course_phase_id (nullable)
└── timestamps

course_plan_subjects (pivot)
├── course_plan_id
├── subject_id
└── order
```

#### 5. **Recrutamento e Seleção**
```
recruitment_types
├── id
├── name (Civil, Especialidade)
└── description

candidates (Candidatos)
├── id
├── recruitment_type_id
├── full_name
├── id_number (Nº Bilhete)
├── gender
├── birth_date
├── marital_status
├── education_level
├── education_area
├── phone
├── email
├── father_name
├── mother_name
├── provenance_id (para agentes)
├── current_rank_id (para agentes)
├── pna_entry_date (para agentes)
├── photo
├── status (pending, approved, rejected, enlisted)
├── academic_year_id
└── timestamps

candidate_documents
├── id
├── candidate_id
├── document_type
├── file_path
├── is_verified
└── timestamps

selection_tests (Testes de Seleção)
├── id
├── name (Documental, Físico, Psicotécnico, Saúde)
├── type (dpq, comando)
├── order
└── timestamps

candidate_test_results
├── id
├── candidate_id
├── selection_test_id
├── result (approved, rejected)
├── score (nullable)
├── observations
├── evaluated_by (user_id)
├── evaluated_at
└── timestamps
```

#### 6. **Formandos/Alunos**
```
students (Formandos)
├── id
├── candidate_id (origem)
├── institution_id
├── course_map_id
├── student_number (matrícula)
├── student_type (civil, mobility, general_regime, special_regime)
├── status (enlisted, recruit, trainee, graduated, expelled, withdrawn)
├── nuri
├── cia
├── platoon (pelotão)
├── section (secção)
├── current_phase_id
├── enrollment_date
└── timestamps

student_phases (Histórico de fases)
├── id
├── student_id
├── course_phase_id
├── status (in_progress, approved, failed)
├── started_at
├── completed_at
└── timestamps
```

#### 7. **Formadores**
```
trainers (Formadores)
├── id
├── institution_id
├── full_name
├── nip
├── gender
├── rank_id
├── organ
├── education_level
├── phone
├── trainer_type (civil, uniformed)
├── photo
├── is_active
└── timestamps

trainer_documents
├── id
├── trainer_id
├── document_type
├── file_path
└── timestamps

trainer_subject_authorizations
├── id
├── trainer_id
├── subject_id
├── course_id
├── authorized_at
├── authorized_by
└── timestamps
```

#### 8. **Turmas e Classes**
```
classes (Turmas)
├── id
├── institution_id
├── course_map_id
├── name
├── academic_year_id
└── timestamps

class_students (pivot)
├── class_id
├── student_id
└── enrolled_at
```

#### 9. **Avaliação**
```
evaluation_methods
├── id
├── name
├── weight
├── course_id
└── timestamps

evaluations
├── id
├── student_id
├── subject_id
├── course_phase_id
├── evaluation_type (punctuality, exam)
├── score
├── observations
├── evaluated_by
├── evaluated_at
└── timestamps

punctuality_records
├── id
├── student_id
├── date
├── status (present, absent, late, justified)
├── observations
├── recorded_by
└── timestamps
```

#### 10. **Dispensas**
```
leave_types
├── id
├── name (illness, special_request, administrative)
└── timestamps

student_leaves (Dispensas)
├── id
├── student_id
├── leave_type_id
├── start_date
├── end_date
├── reason
├── approved_by
├── status (pending, approved, rejected)
└── timestamps
```

#### 11. **Atribuição de Meios**
```
equipment_types
├── id
├── name (uniform, boots, bed, etc.)
└── timestamps

equipment_assignments
├── id
├── student_id
├── equipment_type_id
├── quantity
├── assigned_at
├── returned_at
├── condition
├── assigned_by
└── timestamps
```

---

## 📅 FASES DE IMPLEMENTAÇÃO

### FASE 1: Fundação (Semana 1-2)
- [x] Criar projeto Laravel
- [ ] Configurar Filament com multi-painel
- [ ] Implementar sistema de autenticação
- [ ] Configurar Spatie Permissions
- [ ] Criar migrations base
- [ ] Configurar idioma pt_PT

### FASE 2: Super Admin (Semana 3-4)
- [ ] CRUD Tipos de Instituição
- [ ] CRUD Instituições
- [ ] CRUD Proveniências
- [ ] CRUD Patentes/Postos
- [ ] Gestão de Anos Lectivos
- [ ] CRUD Cursos
- [ ] Mapa de Cursos
- [ ] Planos de Curso com Disciplinas
- [ ] Dashboard Super Admin

### FASE 3: DPQ - Recrutamento (Semana 5-6)
- [ ] Tipos de Recrutamento
- [ ] Cadastro de Candidatos (Civis)
- [ ] Cadastro de Candidatos (Agentes PNA)
- [ ] Gestão de Documentos
- [ ] Testes de Seleção (Documental, Físico, Psicotécnico)
- [ ] Workflow de Aprovação
- [ ] Dashboard DPQ

### FASE 4: Comando Geral (Semana 7)
- [ ] Teste de Saúde
- [ ] Validação Médica
- [ ] Aprovação Final
- [ ] Dashboard Comando

### FASE 5: Instituição de Ensino (Semana 8-10)
- [ ] Recepção de Alunos Alistados
- [ ] Gestão de Formadores
- [ ] Autorização de Disciplinas para Formadores
- [ ] Gestão de Turmas
- [ ] Calendário de Aulas
- [ ] Sistema de Avaliação
- [ ] Controle de Pontualidade
- [ ] Gestão de Fases (Militar/Policial)
- [ ] Gestão de Dispensas
- [ ] Atribuição de Meios/Equipamentos
- [ ] Dashboard Escola

### FASE 6: Relatórios e Auditoria (Semana 11)
- [ ] Logs de Auditoria
- [ ] Relatórios por Instituição
- [ ] Relatórios Globais (Super Admin)
- [ ] Exportação PDF/Excel

### FASE 7: Polimento Final (Semana 12)
- [ ] Testes automatizados
- [ ] Otimização de performance
- [ ] Documentação
- [ ] Deploy

---

## 🔐 MATRIZ DE PERMISSÕES

| Recurso | Super Admin | DPQ | Comando | Escola |
|---------|:-----------:|:---:|:-------:|:------:|
| Instituições | CRUD | R | R | R (própria) |
| Anos Lectivos | CRUD | R | R | R |
| Cursos | CRUD | R | R | R |
| Mapa/Plano Cursos | CRUD | R | R | R |
| Proveniências | CRUD | R | - | - |
| Patentes | CRUD | R | R | R |
| Candidatos | R | CRUD | R | - |
| Testes DPQ | - | CRUD | R | - |
| Teste Saúde | - | R | CRUD | - |
| Formandos | R | R | R | CRUD (próprios) |
| Formadores | R | - | - | CRUD (próprios) |
| Turmas | R | - | - | CRUD |
| Avaliações | R | - | - | CRUD |
| Dispensas | R | - | - | CRUD |
| Equipamentos | R | - | - | CRUD |

---

## 📝 REGRAS DE NEGÓCIO CRÍTICAS

1. **Ano Lectivo Ativo**: Nenhuma operação pode ser realizada fora do ano lectivo ativo
2. **Limite de Formandos**: Não exceder o número máximo definido no Mapa de Cursos
3. **Testes de Seleção**: Candidato deve passar nos 3 testes (Documental, Físico, Psicotécnico) + Saúde
4. **Autorização de Formadores**: Formador só pode lecionar disciplinas previamente autorizadas
5. **Transição de Fases**: Aluno só transita para Fase Policial após aprovação na Fase Militar
6. **Status Instruendo**: Só atribuído na segunda fase do curso
7. **Isolamento de Dados**: Cada instituição só acessa seus próprios dados

---

## 🗂️ ESTRUTURA DE DIRETÓRIOS

```
app/
├── Enums/
│   ├── StudentStatus.php
│   ├── CandidateStatus.php
│   ├── TrainerType.php
│   └── ...
├── Filament/
│   ├── Admin/           (Super Admin Panel)
│   │   ├── Resources/
│   │   ├── Pages/
│   │   └── Widgets/
│   ├── Dpq/             (DPQ Panel)
│   │   ├── Resources/
│   │   ├── Pages/
│   │   └── Widgets/
│   ├── Comando/         (Comando Geral Panel)
│   │   ├── Resources/
│   │   ├── Pages/
│   │   └── Widgets/
│   └── Escola/          (Escola Panel)
│       ├── Resources/
│       ├── Pages/
│       └── Widgets/
├── Models/
├── Policies/
├── Services/
├── Observers/
└── Traits/
    └── BelongsToInstitution.php
```

---

## ✅ PRÓXIMOS PASSOS IMEDIATOS

1. Aguardar conclusão da instalação do Composer
2. Instalar Filament: `composer require filament/filament`
3. Instalar Spatie Permission: `composer require spatie/laravel-permission`
4. Configurar painéis Filament
5. Criar migrations base
6. Implementar seeders iniciais
7. Começar pelos recursos do Super Admin

---

*Documento criado em: 2026-01-10*
*Versão: 1.0*
