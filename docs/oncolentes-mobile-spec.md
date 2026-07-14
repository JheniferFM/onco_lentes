# Especificação Técnica e de Interface — Oncolentes Mobile

## 1. Visão Geral

O Oncolentes é uma solução de triagem territorial e monitoramento oncológico para o SUS, voltada para uso em ambientes com baixa conectividade e necessidade de padronização de imagem. O aplicativo mobile deve permitir que agentes comunitários de saúde capturem imagens de lesões de pele com um kit portátil, registrem dados clínicos básicos e enviem a análise para o back-end Laravel, com contingência territorial quando a integração externa não estiver disponível.

### Objetivos do app
- Capturar imagens padronizadas de lesões de pele com qualidade mínima para análise.
- Registrar dados rápidos de identificação e anamnese.
- Operar offline-first em áreas remotas.
- Enviar dados e imagens para o back-end Laravel quando houver conexão.
- Exibir resultado de triagem, risco estimado e protocolo de acompanhamento.

### Proposta de stack recomendada
- Front-end mobile: Flutter
- Gerenciamento de estado: Riverpod ou Bloc
- Persistência local: SQLite via Drift/Sqflite
- Armazenamento de imagens: diretório local do app + metadados no banco local
- Câmera: plugin de câmera com foco, exposição e validação visual
- Sincronização: fila local com retry e sincronização em background

---

## 2. Fluxo Completo do Usuário

### 2.1 Fluxo principal
1. Abertura do app
2. Boas-vindas e identificação rápida
3. Instruções do kit físico
4. Captura da imagem com câmera inteligente
5. Mini-anamnese clínica
6. Envio/sincronização
7. Tela de resultado e protocolo de triagem

### 2.2 Passo a passo detalhado

#### Tela 1 — Boas-vindas e Identificação Rápida
Objetivo: iniciar o fluxo com o mínimo de atrito possível.

Campos obrigatórios:
- Cartão SUS/CPF
- Nome completo
- Idade
- Cidade
- Estado

Comportamento:
- Validação imediata por campo
- Botão principal: “Continuar”
- Salvamento local automático como rascunho se houver offline

#### Tela 2 — Instruções do Kit
Objetivo: ensinar o usuário a montar o kit antes da captura.

Conteúdo:
- Como acoplar a lente macro ao smartphone
- Como posicionar a lesão no centro da imagem
- Como posicionar a régua clínica de referência
- Como garantir iluminação adequada

Comportamento:
- Passo a passo visual com ilustrações simples
- Botão: “Entendi e quero continuar”
- Reabertura possível das instruções durante o fluxo

#### Tela 3 — Câmera Inteligente
Objetivo: capturar imagem padronizada e qualificada para análise.

Elementos visuais:
- Target visual para alinhamento da lesão
- Guia de enquadramento com referência anatômica
- Indicador de foco
- Indicador de iluminação
- Sobreposição para mostrar se a régua clínica está visível

Validações locais:
- Lesão alinhada ao centro do alvo
- Régua clínica visível
- Foco nítido
- Iluminação adequada
- Evitar imagem borrada, muito escura ou sem referência

Ações:
- “Tirar foto”
- “Repetir”
- “Usar última foto”
- “Avançar”

#### Tela 4 — Mini-Anamnese
Objetivo: registrar sinais clínicos básicos relevantes para triagem.

Perguntas sugeridas:
- Há coceira na lesão?
- Há sangramento?
- A lesão evoluiu nas últimas semanas?
- Há dor ou sensibilidade?

Formato:
- Respostas simples: Sim / Não / Não sei
- Campo opcional de observação
- Botão: “Continuar”

#### Tela 5 — Sincronização e Resultado
Objetivo: mostrar o protocolo de triagem, o risco estimado e o aviso legal.

Conteúdo:
- Status da sincronização
- Protocolo de triagem aplicado
- Risco estimado
- Diagnóstico/label da contingência ou IA
- Confiança estimada
- Aviso legal
- Botão: “Finalizar”

---

## 3. Arquitetura das Telas

### Tela 1 — Boas-vindas e Identificação Rápida
**Conteúdo**
- Logo institucional
- Texto curto de contextualização
- Campos de preenchimento
- Botão de ação

**Regras de UX**
- Fluxo simples
- Campos organizados em ordem lógica
- Feedback imediato de erro

### Tela 2 — Instruções do Kit
**Conteúdo**
- Passo a passo visual
- Ilustrações de montagem
- Botão de confirmação

**Regras de UX**
- Linguagem curta e objetiva
- Ícones visuais claros
- Foco em instrução prática

### Tela 3 — Câmera Inteligente
**Conteúdo**
- Preview da câmera
- Overlays de referência
- Indicadores visuais de qualidade
- Botão de captura

**Regras de UX**
- Interface simples em campo
- Menos distrações
- Feedback em tempo real

### Tela 4 — Mini-Anamnese
**Conteúdo**
- Perguntas curtas
- Botões grandes
- Opção de observação livre

**Regras de UX**
- Fluxo rápido
- Experiência simples para uso em contexto clínico ou comunitário

### Tela 5 — Sincronização e Resultado
**Conteúdo**
- Estado do envio
- Resultado da triagem
- Aviso legal

**Regras de UX**
- Informações claras e não alarmistas
- Destacar ação principal
- Mensagens de erro simples

---

## 4. Requisitos de Arquitetura Tecnológica

### 4.1 Arquitetura recomendada
A estrutura do app deve ser modular e resiliente:
- Camada de UI
- Camada de domínio/serviços
- Camada de persistência local
- Camada de rede/sincronização
- Camada de fila offline

### 4.2 Estratégia Offline-First
O app deve funcionar mesmo sem conexão em regiões remotas.

#### Comportamento esperado
- O agente pode preencher dados, capturar imagem e responder à anamnese sem internet.
- O app salva localmente:
  - dados do paciente
  - respostas da anamnese
  - imagem e metadados
  - status do envio

#### Persistência local
- Dados estruturados: SQLite via Drift/Sqflite
- Imagens: diretório local do app
- Metadados: timestamp, status, tentativas, hash, tipo de imagem

#### Fila de sincronização
Cada registro deve entrar em uma fila local com estados:
- pendente
- sincronizando
- enviado
- erro

Regras recomendadas:
- Retry exponencial
- Evitar duplicidade
- Reprocessar automaticamente ao voltar a conexão
- Exibir estado de envio ao usuário

### 4.3 Integração com o Laravel
O app deve consumir o endpoint do OncoLentesController com payload estruturado e receber:
- status da análise
- risco estimado
- confiança
- label original
- aviso de contingência

---

## 5. Payload JSON Esperado

### Estrutura recomendada

```json
{
  "nome": "Maria Silva",
  "idade": 48,
  "ddd": "11",
  "cidade": "São Paulo",
  "estado": "SP",
  "imagem": "base64_or_file_upload",
  "anamnese": {
    "coceira": true,
    "sangramento": false,
    "evolucao": true,
    "dor": false,
    "observacoes": "Lesão com aumento gradual nas últimas 3 semanas"
  },
  "metadados": {
    "device": "Android",
    "app_version": "1.0.0",
    "captured_at": "2026-07-14T10:30:00Z",
    "offline": false
  }
}
```

### Recomendação de transporte
- Enviar imagem como multipart/form-data
- Dados textuais em JSON ou multipart fields
- Nome e MIME corretos para a imagem
- O backend Laravel deve aceitar o payload de forma compatível com upload de arquivo

---

## 6. Requisitos de UX/UI e Acessibilidade

### 6.1 Para agentes comunitários de saúde
A interface deve ser simples e resiliente para uso em campo.

Recomendações:
- Fontes legíveis e grandes
- Botões grandes e claros
- Textos curtos e objetivos
- Ícones consistentes
- Fluxo linear com poucas telas por etapa
- Feedback visual claro para cada ação

### 6.2 Para pacientes de diferentes idades
- Fonte mínima recomendada: 16px
- Alto contraste entre texto e fundo
- Área de toque mínima de 44x44 px
- Linguagem simples e direta
- Instruções visuais acompanhadas de texto curto

### 6.3 Acessibilidade
- Suporte a TalkBack e VoiceOver
- Labels semânticas para campos
- Navegação acessível por tela e teclado
- Mensagens de erro claras e objetivas
- Feedback visual e sonoro para falhas de câmera
- Evitar dependência exclusiva de cor para comunicação

### 6.4 Instruções visuais para o kit físico
- Mostrar exemplo de enquadramento ideal
- Destacar a régua clínica como referência
- Exibir mensagens de correção em linguagem simples
- Mostrar o que é aceitável e o que deve ser repetido

---

## 7. Requisitos Não Funcionais

### Segurança e privacidade
- Armazenamento seguro dos dados locais
- Consentimento explícito para captura e envio
- Retenção mínima de dados sensíveis
- Criptografia local recomendada para dados críticos

### Performance
- Resposta rápida nas telas de captura
- Compressão inteligente de imagem antes do envio
- Evitar travamentos durante a sincronização

### Confiabilidade
- Operar mesmo com rede instável
- Reprocessar mensagens com falha
- Mostrar claramente o estado do envio ao usuário

---

## 8. Resumo do Modelo de Produto

O app Oncolentes deve ser:
- simples para uso em campo,
- confiável em ambientes remotos,
- acessível para diferentes perfis de usuário,
- preparado para offline-first,
- integrado ao back-end Laravel com contingência territorial.

---

## 9. Próximos Passos Recomendados

1. Definir MVP com telas prioritárias.
2. Criar wireframes de baixa fidelidade.
3. Definir modelo de dados local.
4. Implementar fluxo de câmera com validação visual.
5. Implementar fila offline e sincronização assíncrona.
6. Integrar com o endpoint Laravel de análise.
