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
// Project implemented by the "Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos

/**
 * Version details
 *
 * @package    local_mail
 * @copyright  2012-2014 Institut Obert de Catalunya <https://ioc.gencat.cat>
 * @copyright  2014-2017 Marc Català <reskit@gmail.com>
 * @copyright  2016-2017 Albert Gasset <albertgasset@fsfe.org>
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addrecipients'] = 'Afegeix destinataris';
$string['all'] = 'Tots';
$string['allcourses'] = 'Tots els cursos';
$string['allgroups'] = 'Tots els grups';
$string['allroles'] = 'Tots els rols';
$string['allusers'] = 'Tots els usuaris';
$string['apply'] = 'Aplica';
$string['bcc'] = 'C/o';
$string['cancel'] = 'Cancel·la';
$string['cannotsendmailtouser'] = 'No podeu enviar correu a aquest usuari en aquest curs';
$string['cc'] = 'A/c';
$string['changecourse'] = 'Canvia el curs';
$string['clearsearch'] = 'Esborra la cerca';
$string['close'] = 'Tanca';
$string['color'] = 'Color';
$string['colorblue'] = 'Blau';
$string['colorcyan'] = 'Cian';
$string['colorgray'] = 'Gris';
$string['colorgreen'] = 'Verd';
$string['colorindigo'] = 'Indi';
$string['colororange'] = 'Taronja';
$string['colorpink'] = 'Rosa';
$string['colorpurple'] = 'Porpra';
$string['colorred'] = 'Vermell';
$string['colorteal'] = 'Xarxet';
$string['coloryellow'] = 'Groc';
$string['compose'] = 'Redacta';
$string['configcoursebadges'] = 'Etiquetes de curs';
$string['configcoursebadgesdesc'] = 'Estableix el tipus de nom de curs mostrat als missatges.';
$string['configcoursebadgeslength'] = 'Longitud de les etiquetes de curs.';
$string['configcoursebadgeslengthdesc'] = 'Limita la longitud de les etiquetes de curs a aquest nombre aproximat de caràcters.';
$string['configcourselink'] = 'Enllaç al curs';
$string['configcourselinkdesc'] = 'Mostra un enllaç al curs actual a la part superior de la pàgina.';
$string['configcoursetrays'] = 'Safates de curs';
$string['configcoursetraysdesc'] = 'Estableix quins cursos es mostren als menús.';
$string['configcoursetraysname'] = 'Nom de les safates de curs';
$string['configcoursetraysnamedesc'] = 'Estableix el tipus de nom de curs que es mostra als menús.';
$string['configenablebackup'] = 'Còpia de seguretat / restauració';
$string['configenablebackupdesc'] = 'Habilita les còpies de seguretat i la restauració de missatges de correu i etiquetes.';
$string['configfilterbycourse'] = 'Filtre por curs';
$string['configfilterbycoursedesc'] = 'Estableix el tipus de nom de curs utilitzat al filtre per curs.';
$string['configglobaltrays'] = 'Safates globals';
$string['configglobaltraysdesc'] = 'Estableix quines safates globals es mostren als menús. La safata d\'entrada sempre és visible.';
$string['configincrementalsearch'] = 'Cerca instantània';
$string['configincrementalsearchdesc'] = 'Habilita la visualització de resultats mentre l\'usuari escriu al quadre de cerca.';
$string['configincrementalsearchlimit'] = 'Límit de la cerca ràpida';
$string['configincrementalsearchlimitdesc'] = 'Estableix el nombre màxim de missatges recents inclosos a la cerca instantània. Augmentar aquest nombre pot tenir un impacte negatiu al rendiment de la base de dades.';
$string['configmaxattachments'] = 'Nombre de fitxers adjunts';
$string['configmaxattachmentsdesc'] = 'Estableix el nombre màxim de fitxers adjunts per missatge.';
$string['configmaxattachmentsize'] = 'Mida dels fitxers adjunts';
$string['configmaxattachmentsizedesc'] = 'Estableix la mida màxima dels fitxers adjunts per missatge.';
$string['configmaxrecipients'] = 'Nombre de destinataris';
$string['configmaxrecipientsdesc'] = 'Estableix el nombre màxim de destinataris permesos per missatge.';
$string['configusersearchlimit'] = 'Límit de la cerca d\'usuaris';
$string['configusersearchlimitdesc'] = 'Estableix el nombre màxim de resultats mostrats en la cerca d\'usuaris.';
$string['course'] = 'Curs';
$string['courseswithunreadmessages'] = 'Cursos amb missatges no llegits';
$string['create'] = 'Crea';
$string['date'] = 'Data';
$string['deleteforever'] = 'Suprimeix definitivament';
$string['deleteforevermessageconfirm'] = 'Esteu segur que voleu suprimir definitivament el missatge?';
$string['deleteforeverselectedconfirm'] = 'Esteu segur que voleu suprimir definitivament els missatges seleccionats?';
$string['deletelabel'] = 'Suprimeix l\'etiqueta';
$string['deletelabelconfirm'] = 'Esteu segur que voleu suprimir definitivament l\'etiqueta «{$a}»?';
$string['downloadall'] = 'Baixa\'ls tots';
$string['draft'] = 'Esborrany';
$string['drafts'] = 'Esborranys';
$string['draftsaved'] = 'S\'ha desat l\'esborrany';
$string['editlabel'] = 'Edita l\'etiqueta';
$string['emptytrash'] = 'Buida la paperera';
$string['emptytrashconfirm'] = 'Esteu segur que voleu suprimir definitivament tots els missatges de la paperera?';
$string['error'] = 'Error';
$string['errorcoursenotfound'] = 'No s\'ha trobat el curs amb ID {$a}';
$string['erroremptylabelname'] = 'El nom d\'etiqueta està buit';
$string['erroremptyrecipients'] = 'El missatge no té destinatari';
$string['erroremptysubject'] = 'L\'assumpte del missatge està buit';
$string['errorinvalidrecipients'] = 'Alguns destinataris no són vàlids';
$string['errorlabelnotfound'] = 'No s\'ha trobat l\'etiqueta amb ID {$a}';
$string['errormessagenotfound'] = 'No s\'ha trobat el missatge amb ID {$a}';
$string['errornocourses'] = 'No teniu permís per enviar o rebre correu en cap curs';
$string['errorpluginnotinstalled'] = 'El connector Correu no està instal·lat o actualitzat correctament';
$string['errorrepeatedlabelname'] = 'Ja existeix una etiqueta amb aquest nom';
$string['errortoomanyrecipients'] = 'El missatge supera el límit permès de {$a} destinataris';
$string['errorusernotfound'] = 'No s\'ha trobat l\'usuari amb ID {$a}';
$string['eventdraftcreated'] = 'Esborrany creat';
$string['eventdraftdeleted'] = 'Esborrany eliminat';
$string['eventdraftupdated'] = 'Esborrany actualitzat';
$string['eventdraftviewed'] = 'Esborrany vist';
$string['eventmessagesent'] = 'Missatge enviat';
$string['eventmessageviewed'] = 'Missatge vist';
$string['forward'] = 'Reenvia';
$string['forwardedmessage'] = 'Missatge reenviat';
$string['from'] = 'De';
$string['gotocourse'] = 'Ves al curs {$a}';
$string['hasattachments'] = 'Conté fitxers adjunts';
$string['help'] = 'Ajuda';
$string['inbox'] = 'Safata d\'entrada';
$string['labels'] = 'Etiquetes';
$string['locked'] = 'Bloquejat';
$string['mail:mailsamerole'] = 'Envia correu a usuaris amb el mateix rol';
$string['mail:usemail'] = 'Utilitza el correu';
$string['markasread'] = 'Marca com a llegit';
$string['markasstarred'] = 'Marca com a destacat';
$string['markasunread'] = 'Marca com a no llegit';
$string['markasunstarred'] = 'Marca com a no destacat';
$string['markmessagesasread'] = 'Marca els missatges com a llegits';
$string['messagelist'] = 'Llista de missatges';
$string['messagemovedtotrash'] = 'S\'ha mogut un missatge a la paperera';
$string['messageprovider:mail'] = 'Notificació de correu';
$string['messagerestored'] = 'S\'ha restaurat un missatge';
$string['messages'] = 'Missatges';
$string['messagesent'] = 'S\'ha enviat el missatge';
$string['messagesmovedtotrash'] = 'S\'han mogut {$a} missatges a la paperera';
$string['messagesperpage'] = 'Missatges per pàgina';
$string['messagesrestored'] = 'S\'han restaurat {$a} missatges';
$string['more'] = 'Més';
$string['movetotrash'] = 'Mou a la paperera';
$string['name'] = 'Nom';
$string['navigation'] = 'Navegació';
$string['newlabel'] = 'Etiqueta nova';
$string['newmail'] = 'Correu nou';
$string['nextmessage'] = 'Missatge següent';
$string['nextpage'] = 'Pàgina següent';
$string['nocoursematchestext'] = 'Cap curs coincideix amb el text introduït';
$string['nomessagesfound'] = 'No s\'han trobat missatges';
$string['none'] = 'Cap';
$string['norecipient'] = '(sense destinataris)';
$string['nosubject'] = '(sense assumpte)';
$string['notifications'] = 'Notificacions';
$string['notificationsmallmessage'] = '{$a->user} us ha enviat un missatge al curs {$a->course}';
$string['notificationsubject'] = 'Nou correu a {$a}';
$string['nousersfound'] = 'No s\'han trobat usuaris';
$string['pagingrange'] = '{$a->first}-{$a->last}';
$string['pagingrangetotal'] = '{$a->first}-{$a->last} de {$a->total}';
$string['pagingsingle'] = '{$a->index} de {$a->total}';
$string['pluginname'] = 'Correu';
$string['preferences'] = 'Preferències';
$string['previousmessage'] = 'Missatge anterior';
$string['previouspage'] = 'Pàgina anterior';
$string['read'] = 'Llegits';
$string['references'] = 'Referències';
$string['remove'] = 'Suprimeix';
$string['reply'] = 'Respon';
$string['replyall'] = 'Respon a tothom';
$string['restore'] = 'Restaura';
$string['restoremessageconfirm'] = 'Esteu segur que voleu restaurar els missatges seleccionats?';
$string['save'] = 'Desa';
$string['search'] = 'Cerca';
$string['searchallmessages'] = 'Cerca tots els missatges';
$string['searchdate'] = 'Data';
$string['searchdatehelp'] = 'Cerca missatges amb aquesta data o una data anterior.';
$string['searchfrom'] = 'De';
$string['searchhasattachments'] = 'Conté fitxers adjunts';
$string['searchoptions'] = 'Opcions de cerca';
$string['searchto'] = 'Per a';
$string['searchunreadonly'] = 'Només sense llegir';
$string['select'] = 'Selecciona';
$string['send'] = 'Envia';
$string['sendmail'] = 'Envia correu';
$string['sentplural'] = 'Enviats';
$string['showrecentmessages'] = 'Mostra els missatges recents';
$string['starred'] = 'Destacat';
$string['starredplural'] = 'Destacats';
$string['subject'] = 'Assumpte';
$string['to'] = 'Per a';
$string['toomanyusersfound'] = 'S\'han trobat més usuaris dels que es poden mostrar. Introduïu un text o seleccioneu un rol o un grup per restringir la cerca.';
$string['trash'] = 'Paperera';
$string['trays'] = 'Safates';
$string['undo'] = 'Desfés';
$string['unread'] = 'Sense llegir';
$string['unstarred'] = 'Sense destacar';
$string['viewmessage'] = 'Visualitza el missatge';
