<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * local_ai_bridge.php
 *
 * @package   local_ai_bridge
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addconnection'] = 'Adicionar conexão';
$string['addpurpose'] = 'Adicionar finalidade';
$string['addrole'] = 'Adicionar papel de IA';
$string['addroute'] = 'Adicionar rota';
$string['addtenant'] = 'Adicionar tenant';
$string['addtenantadmin'] = 'Adicionar administrador do tenant';
$string['adjustcredits'] = 'Ajustar créditos';
$string['adminsrestricted'] = 'Apenas administradores do site com permissão para gerenciar tenants podem delegar administradores de tenants.';
$string['ai_bridge:manageall'] = 'Gerenciar todos os tenants do AI Bridge';
$string['ai_bridge:managetenants'] = 'Criar e gerenciar tenants do AI Bridge';
$string['ai_bridge:use'] = 'Usar o AI Bridge';
$string['ai_bridge:viewdetailedstats'] = 'Ver estatísticas detalhadas do AI Bridge';
$string['ai_bridge:viewuserstats'] = 'Ver estatísticas por usuário do AI Bridge';
$string['airole'] = 'Papel de IA';
$string['amount'] = 'Quantidade';
$string['anyrole'] = 'Qualquer papel de IA';
$string['chooseprovider'] = 'Escolha o provedor';
$string['connection'] = 'Conexão';
$string['connectionname'] = 'Nome da conexão';
$string['connections'] = 'Conexões';
$string['creditamount'] = 'Quantidade de créditos';
$string['creditbalance'] = 'Saldo de créditos: {$a}';
$string['creditcost'] = 'Créditos por requisição bem-sucedida';
$string['credits'] = 'Créditos';
$string['creditused'] = 'Créditos usados';
$string['defaultrole'] = 'Papel padrão';
$string['detailedstats'] = 'Estatísticas detalhadas';
$string['editconnection'] = 'Editar conexão';
$string['editpurpose'] = 'Editar finalidade';
$string['editrole'] = 'Editar papel de IA';
$string['editroute'] = 'Editar rota';
$string['editusercontrol'] = 'Editar acesso do usuário à IA';
$string['enabled'] = 'Ativo';
$string['error:allroutesfailed'] = 'Todas as rotas de IA configuradas falharam.';
$string['error:bridgeunavailable'] = 'O provedor de IA “{$a}” não está instalado ou está indisponível.';
$string['error:configdecrypt'] = 'Não foi possível descriptografar a configuração do provedor de IA.';
$string['error:creditlock'] = 'A contabilização de créditos de IA está temporariamente ocupada. Tente novamente.';
$string['error:invalidendpoint'] = 'A URL do endpoint do provedor é inválida.';
$string['error:noroute'] = 'Nenhuma rota de IA está configurada para esta finalidade e este papel.';
$string['error:notenant'] = 'Nenhum tenant de IA ativo corresponde ao perfil deste usuário.';
$string['error:purposeunavailable'] = 'A finalidade de IA “{$a}” não está disponível para este tenant.';
$string['error:tenantcredits'] = 'O tenant não possui créditos de IA suficientes.';
$string['error:usercredits'] = 'O limite de créditos de IA do usuário foi atingido.';
$string['error:userdisabled'] = 'O acesso à IA está desativado para este usuário no tenant.';
$string['error:usernotfound'] = 'Nenhum usuário ativo do Moodle foi encontrado com este endereço de e-mail.';
$string['estimatedcost'] = 'Custo estimado do provedor';
$string['inputtokens'] = 'Tokens de entrada';
$string['local/ai_bridge:manageall'] = 'Gerenciar todos os tenants do AI Bridge';
$string['local/ai_bridge:managetenants'] = 'Criar e gerenciar tenants do AI Bridge';
$string['local/ai_bridge:use'] = 'Usar o AI Bridge';
$string['local/ai_bridge:viewdetailedstats'] = 'Visualizar estatísticas detalhadas do AI Bridge';
$string['local/ai_bridge:viewuserstats'] = 'Visualizar estatísticas do AI Bridge por usuário';
$string['manage'] = 'Gerenciar AI Bridge';
$string['maxoutputtokens'] = 'Máximo de tokens de saída';
$string['model'] = 'Modelo';
$string['note'] = 'Observação';
$string['outputtokens'] = 'Tokens de saída';
$string['overview'] = 'Visão geral';
$string['pluginname'] = 'AI Bridge';
$string['priority'] = 'Prioridade';
$string['privacy:metadata:admin'] = 'Atribuições de administradores delegados de tenants.';
$string['privacy:metadata:admin:userid'] = 'ID do usuário Moodle designado como administrador do tenant.';
$string['privacy:metadata:credit'] = 'Registros do extrato de créditos associados ao uso de IA e aos ajustes administrativos.';
$string['privacy:metadata:credit:actorid'] = 'Usuário Moodle que realizou o ajuste de créditos, quando aplicável.';
$string['privacy:metadata:credit:amount'] = 'Quantidade de créditos adicionados ou removidos.';
$string['privacy:metadata:credit:note'] = 'Observação administrativa opcional sobre o ajuste de créditos.';
$string['privacy:metadata:credit:timecreated'] = 'Data de criação do registro no extrato de créditos.';
$string['privacy:metadata:credit:type'] = 'Tipo do registro no extrato.';
$string['privacy:metadata:credit:userid'] = 'Usuário Moodle cujo uso de IA consumiu créditos, quando aplicável.';
$string['privacy:metadata:usage'] = 'Metadados de uso da IA. Prompts e conteúdos gerados não são armazenados nos registros de uso.';
$string['privacy:metadata:usage:bridge'] = 'Subplugin do provedor utilizado.';
$string['privacy:metadata:usage:credits'] = 'Créditos internos do tenant consumidos.';
$string['privacy:metadata:usage:inputtokens'] = 'Quantidade de tokens de entrada informada pelo provedor.';
$string['privacy:metadata:usage:model'] = 'Modelo de linguagem configurado.';
$string['privacy:metadata:usage:outputtokens'] = 'Quantidade de tokens de saída informada pelo provedor.';
$string['privacy:metadata:usage:timecreated'] = 'Data do registro da requisição de IA.';
$string['privacy:metadata:usage:userid'] = 'ID do usuário Moodle.';
$string['privacy:metadata:user'] = 'Configurações de acesso à IA por usuário e tenant.';
$string['privacy:metadata:user:creditlimit'] = 'Limite individual de créditos opcional.';
$string['privacy:metadata:user:creditused'] = 'Quantidade de créditos do tenant consumidos pelo usuário.';
$string['privacy:metadata:user:enabled'] = 'Indica se o acesso à IA está ativo para o usuário.';
$string['privacy:metadata:user:roleid'] = 'Papel lógico de IA atribuído ao usuário.';
$string['privacy:metadata:user:userid'] = 'ID do usuário Moodle.';
$string['provider'] = 'Provedor';
$string['purpose'] = 'Finalidade';
$string['purposes'] = 'Finalidades';
$string['requests'] = 'Requisições';
$string['roles'] = 'Papéis de IA';
$string['routes'] = 'Rotas';
$string['setting:autocreatetenants'] = 'Criar tenants automaticamente';
$string['setting:autocreatetenants_desc'] = 'Cria registros de tenants a partir dos campos de perfil do usuário quando não existe um tenant correspondente.';
$string['setting:logfailures'] = 'Registrar requisições com falha';
$string['setting:logfailures_desc'] = 'Armazena metadados do provedor, modelo e código de erro para rotas que falharam. Os registros de falha não armazenam prompts nem respostas.';
$string['setting:tenantkey'] = 'Chave do tenant no perfil';
$string['setting:tenantkey_desc'] = 'Selecione quais campos padrão do usuário Moodle identificam um tenant.';
$string['subplugintype_aibridge'] = 'Provedor AI Bridge';
$string['subplugintype_aibridge_plural'] = 'Provedores AI Bridge';
$string['systeminstruction'] = 'Instrução de sistema';
$string['task:synctenants'] = 'Sincronizar tenants do AI Bridge a partir dos perfis dos usuários';
$string['temperature'] = 'Temperatura';
$string['tenantadmins'] = 'Administradores do tenant';
$string['tenantkey:department'] = 'Departamento';
$string['tenantkey:institution'] = 'Instituição';
$string['tenantkey:institution_department'] = 'Instituição + departamento';
$string['tenantprofile'] = 'Mapeamento de perfil: instituição “{$a->institution}”, departamento “{$a->department}”.';
$string['totaltokens'] = 'Total de tokens';
$string['type'] = 'Tipo';
$string['usercreditlimit'] = 'Limite individual de créditos';
$string['usercreditlimit_help'] = 'Deixe em branco para usar apenas o saldo compartilhado do tenant. O contador é cumulativo para este registro de usuário no tenant.';
$string['usershint'] = 'A ativação do usuário, o papel de IA e o limite individual de créditos são armazenados por tenant. Os usuários sem registro explícito herdam o papel padrão do tenant e começam com acesso ativo.';
$string['userstats'] = 'Estatísticas por usuário';
