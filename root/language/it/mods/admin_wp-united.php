<?php
/** 
*
* WP-United [Italian]
*
* @package WP-United
* @version $Id: wp-united.php,v 0.7.0 japgalaxy (japgalaxy.altervista.org) 2009/05/18
* @copyright (c) 2006, 2007 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
// General Public License for more details.
// 
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
//

/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
    $lang = array();
}
// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'ACP_WPU_INDEX_TITLE'	=> 'WP-United',
	    
	'WPU_Default_Version' => 'v0.7.0',
	'WP_Version_Text' => 'WP-United %s',

	'WP_Title' => 'Impostazioni di WP-United',
	'WP_Intro1' => 'Benvenuto in WP-United.',
	'WP_Intro2' => 'Le seguenti impostazioni devono essere impostate correttamente per far s&icirc; che la Mod funzioni correttamente.',
	'WP_Settings' => 'Impostazioni base',
	'WP_UriTitle' => 'URL base di WordPress',
	'WP_UriExplain' => 'Questo &egrave; l\'URL completo in cui Wordpress &egrave; installato. Devi inserire l\'URL che Wordpress avrebbe se non fosse integrato in phpBB. Ad esempio: http://www.example.com/wordpress/ se Wordpress si trova nella cartella "wordpress" o http://www.example.com/ se Wordpress si trova nella root.',
	'WP_UriProb' => 'Non riesco a trovare l\'installazione di Wordpress nell\'URL inserito. Assicurati che questo campo contenga l\'intero URL alla tua installazione di Wordpress.',
	'WP_PathTitle' => 'Percorso completo di WordPress',
	'WP_PathExplain' => 'Questa &egrave; il percorso completo di Wordpress. Se lasci vuoto questo campo, cercheremo di individuarlo automaticamente. Se il rilevamento automatico non funziona, potrebbe essere necessario digitare il percorso completo della tua installazione di Wordpress. Un esempio di Percorso completo &egrave;: "/home/public/www/wordpress" - ma ogni Host ne usa uno diverso.',
	'WP_PathProb' => 'Non riesco a trovare l\'installazione di Wordpress in questo percorso, o a rilevare automaticamente il percorso corretto. Si prega di digitare il percorso corretto qui.',
	'WP_Submit' => 'Invia',
	'WP_Login_Title' => 'Lascia che sia phpBB a gestire automaticamente il Login di WordPress',
	'WP_Login_Explain' => 'Se si attiva questa opzione, phpBB creer&agrave; un account utente in Wordpress la prima volta che ogni utente di phpBB effettuer&agrave; il login. Se il tuo blog Wordpress non prevede l\'interazione di utenti terzi (ad esempio, un blog di una singola persona, un portale o qualsiasi altro tipo di sito web in cui non vi sia possibilit&agrave; di commentare i posts), puoi disattivare questa opzione, in quanto i lettori non necessitano di un account Wordpress. Puoi anche integrare gli utenti di Wordpress preesistenti in phpBB, utilizzando l\'apposito tool "Tool per l\'Integrazione degli utenti" che verr&agrave; visualizzato una volta attivata questa opzione. <br /> Vi ricordo che &egrave; possibile impostare i permessi di WP-United, per ogni utente e/o gruppo, usando il sistema di gestione dei permessi di phpBB3.',
	'WP_Yes' => 'Si',
	'WP_No' => 'No',
	'WP_Footer' => 'WP-United is freely developed by John Wells, www.wp-united.com . If you find this useful, please donate!',

	'WPU_Must_Upgrade_Title' => 'Aggiornamento Rilevato',
	'WPU_Must_Upgrade_Explain1' => 'Hai aggiornato WP-United. &Egrave; necessario eseguire l\'%sUpgrade Tool%s prima di continuare.<br /><br />Poi potrai tornare qui ed eseguire l\'Installazione Guidata.',
	'WPU_Must_Upgrade_Explain2' => '[Nota: Se non hai mai attivato l\'Tool per l\'Integrazione degli utenti, puoi %scliccare qui%s per ignorare questo prompt.]',
	'WPU_Not_Installed' => '(Non Installato)',


	'WPU_URL_Not_Provided' => 'ERRORE: Non &egrave; stato fornito alcun URL di WordPress! Il percorso completo pu&ograve; essere ottenuto automaticamente, ma tu DEVI inserire l\'URL in cui WordPress &egrave; stato installato. Per favore riprova.',
	'WPU_URL_Diff_Host' => 'ATTENZIONE: L\'URL di Wordpress inserito sembra essere su un altro dominio rispetto a phpBB. Il tuo Wordpress deve essere installato nello stesso host in cui &egrave; installato phpBB. Non sar&agrave; possibile rilevare automaticamente il percorso completo.',
	'WPU_OK' => 'OK',
	'WPU_URL_No_Exist' => 'ATTENZIONE: L\'URL di Wordpress inserito non sembra esistere (restituisce un errore 404)! Controlla d\'averlo digitato correttamente, e che Wordpress sia gi&agrave; stato installato.',
	'WPU_Cant_Autodet' => 'ERRORE: Il percorso non pu&ograve; essere rilevato automaticamente dall\'URL che hai fornito poich&eacute; sembra essere su un altro host. Correggi l\'URL o inserisci manualmente il percorso completo di Wordpress.',
	'WPU_Path_Autodet' => 'Sto cercando di rilevare automaticamente il percorso...',
	'WPU_Autodet_Error' => 'ERRORE: Il percorso completo della vostra installazione di Wordpress non pu&ograve; essere rilevato automaticamente. Per favore digitalo manualmente.<br />',
	'WPU_Pathfind_Warning' => 'ATTENZIONE: Non &egrave; stata trovata un\'installazione di Wordpress funzionante. O il percorso non &egrave; stato inserito correttamente o non &egrave; stato autorilevato correttamente oppure ancora Wordpress non &egrave; stato ancora installato. Controlla il percorso fornito e riprova.',
	'WPU_Conn_InstallError' => 'ERRORE: La connessione di WP-United potrebbe non essere stata installata. Controlla che il percorso fornito o auto-rilevato sia corretto, e che Wordpress sia installato e funzionante.<br />',
	'WPU_PathIs' => "File path => ",
	'WPU_Checking_URL' => 'Sto controllando l\'URL di WordPress: ',
	'WPU_Process_Settings' => 'Elaborazione delle impostazioni in corso...',
	'WPU_Checking_URL' => 'Sto verificando che l\'URL esista...',
	'WPU_Checking_WPExists' => 'Sto verificando che esista un\'installazione di Wordpress qui... ',
	'WPU_Conn_Installing' => 'Sto installando la Connessione di WP-United... ',


	'WP_AllOK' => 'Tutto OK! Le tue impostazioni sono state salvate.',
	'WP_Saved_Caution' => 'Le tue impostazioni sono state salvate, ma probabilmente non funzioneranno fino a quando non correggerete gli errori sopra riportati.',
	'WP_Errors_NoSave' => 'Si sono riscontrati degli errori e le tue impostazioni non sono state salvate. Torna indietro e correggi gli errori sopra riportati.',
	'WP_DBErr_Retrieve' => 'Impossibile accedere alla tabella di integrazione di Wordpress nel database. Assicurati che sia stata eseguita l\'apposita query SQL al momento dell\'installazione della mod.',
	'WP_DBErr_Write' => 'Non &egrave; stato possibile inserire i valori nel database. Assicurati che sia stata eseguita l\'apposita query SQL al momento dell\'installazione della mod.',
	'WP_Config_GoBack' => 'Clicca %sQui%s per tornare alla Pagina principale di Amministrazione di WP-United.',
	'WP_Perm_Title' => 'PhpBB &lt;-&gt; WordPress Mappa dei Permessi (richiede JavaScript)',

	'WP_Perm_Explain1' => 'Se hai scelto che phpBB gestisca automaticamente il login di WordPress, puoi assegnare, mediante il sistema di gestione dei permessi di phpBB3, agli utenti e/o ai gruppi di phpBB dei Ruoli Utente di WordPress. Fai molta attenzione a non concedere autorizzazioni importanti a chiunque!',
	'WP_Perm_Explain2' => 'Sulla sinistra ci sono i permessi basati sui Gruppi di phpBB (come gli utenti registrati, i moderatori e gli amministratori) e i gruppi di phpBB definiti dagli utenti. Le caselle a destra rappresentano i permessi di Wordpress.',
	'WP_Perm_Explain3' => 'Clicca su un gruppo di phpBB sulla sinistra, quindi selezionare un riquadro a destra corrispondente alle autorizzazioni di Wordpress che si desidera assegnare ad ogni membro di tale gruppo, quindi, clicca sulla freccia a destra per assegnarle. I gruppi del riquadro di sinistra non saranno integrati. Per una corretta visione delle autorizzazioni di Wordpress, consulta questa pagina: <a href=>"http://codex.wordpress.org/Roles_and_Capabilities">WordPress Codex Roles and Capabilities</a>.',
	'WP_Perm_Explain4' => 'ATTENZIONE - ASSICURATI DI AVER CAPITO QUESTO: quando si verifica un conflitto tra i permessi, il livello di permessi pi&ugrave; alto sovrascriver&agrave; il livello pi&ugrave; basso. Ad esempio, se un membro appartiene a pi&ugrave; di un gruppo, egli otterr&agrave; il livello di permessi pi&ugrave; alto dei due gruppi. Allo stesso modo, se un utente &egrave; un Moderatore, gli verranno assegnati i permessi del gruppo \'Moderatori\' se questo &egrave; il gruppo pi&ugrave; elevato tra quelli di cui &egrave; membro.',

	'WP_Login_Head' => 'Impostazioni per il login e per i permessi di integrazione',

	'WP_User_Title' => 'Utenti Registrati di PhpBB',

	'WP_User_Explain' => 'Imposta che ruolo un normale utente di PhpBB deve avere in Wordpress.',
	'WP_Mod_Title' => 'Moderatori di PhpBB',

	'WP_Mod_Explain' => 'Imposta che ruolo i Moderatori di PhpBB devono avere in WordPress.',

	'WP_Admin_Title' => 'Amministratori di PhpBB',

	'WP_Admin_Explain' => 'Imposta che ruolo un Amministratore di PhpBB deve avere in WordPress. NOTA: Il nome utente predefinito Wordpress \'admin\', non sar&agrave; integrato e conserver&agrave; piena di diritti di amministratore.',

	'WP_Advanced_Head' => 'Impostazioni di visualizzazione',
	'WP_DTD_Title' => 'Utilizza dei Document Type Declaration diversi?',
	'WP_DTD_Explain' => 'Il Document Type Declaration, abbreviato con DTD, &egrave; previsto nella parte superiore di tutte le pagine web per far conoscere al browser che tipo di linguaggio di markup viene utilizzato.<br /><br />Il tema prosilver di phpBB3 usa come tipo predefinito l\'XHTML 1.0 Strict DTD. La maggior parte dei temi di WordPress, invece, usano l\'XHTML 1 transitional DTD.<br /><br />Nella maggior parte dei casi, questo non &egrave; importante -- tuttavia, se vuoi usare il DTD usato da WordPress nelle pagine in cui WordPress &egrave; integrato nel template di phpBB, puoi attivare questa opzione. Ci&ograve; dovrebbe evitare di far andare il browsers in &quot;quirks mode&quot;, e far&agrave; in modo che la maggior parte dei temi per Wordpress saranno visualizzati cos&igrave; come sono stati concepiti.',
	'WP_Role_None' => 'Nessuno',
	'WP_Role_Subscriber' => 'Sottoscrittore',
	'WP_Role_Contributor' => 'Collaboratore',
	'WP_Role_Author' => 'Autore',
	'WP_Role_Editor' => 'Editore',
	'WP_Role_Administrator' => 'Amministratore',

	//Main Page
	'WP_Main_Title' => 'Benvenuto in WP-United v0.7.0',
	'WP_Main_Intro' => 'Da qui &egrave; possibile impostare WP-United, e scegliere le impostazioni necessarie per farla funzionare nel modo desiderato.',
	'WP_Main_IntroFirst' => 'Non hai ancora installato la mod. Ti consigliamo di eseguire l\'Installazione Guidata che puoi trovare qui di seguito.',
	'WP_Main_IntroAdd' => 'Hai gi&agrave; installato la mod. &Egrave; possibile cambiare le opzioni cliccando sul pulsante "Impostazioni", qui di seguito.',
	'WP_Recommended' => 'Consigliato',
	'WP_Wizard_Title' => 'Installazione Guidata',
	'WP_Detailed_Title' => 'Impostazioni',
	'WP_Wizard_Explain' => 'Questa procedura guidata vi guider&agrave; attraverso la configurazione iniziale di WP-United. Nota che &egrave; necessario eseguirlo fino all\'ultimo passo.',
	'WP_Wizard_ExplainFirst' => 'Questa procedura guidata vi guider&agrave; attraverso la configurazione iniziale di WP-United. Poich&egrave; non hai ancora installato la Mod, assicurati di installare prima essa.',
	'WP_Detailed_ExplainFirst' => 'Qui &egrave; possibile vedere e reimpostare tutte le impostazioni di WP-United. Poich&egrave; non hai ancora installato la Mod, assicurati di installare prima essa.',
	'WP_Detailed' => 'Qui &egrave; possibile vedere e reimpostare tutte le impostazioni di WP-United.',
	'WP_Detailed_Explain' => 'Da qui potrai mostrare e modificare tutte le impostazioni in una singola pagina.',
	'WP_SubmitWiz' => 'Installazione Guidata',
	'WP_SubmitDet' => 'Impostazioni',
	'WP_MapLink_Title' => 'Tool per l\'Integrazione degli utenti',
	'WP_MapLink' => 'Vai',
	'WP_MapLink_Explain' => 'L\'Tool per l\'Integrazione degli utenti &egrave; attivata. Per vedere lo stato degli utenti di Wordpress, clicca sul bottone "Tool per l\'Integrazione degli utenti".',

	'WP_Support' => 'Help Support WP-United',
	'WP_Support_Explain' => 'WP-United &egrave; software libero, e ci auguriamo che sia utile. Se &egrave; di tuo gradimento ti invitiamo a sostenerci con una donazione! Ogni somma, per quanto piccola, &egrave; sempre molto apprezzata. Grazie!<br /><br />Il link di PayPal ti porter&agrave; ad una pagina in cui potrai effettuare una donazione al nostro account di PayPal.',



	//Wizard
	'WP_Wizard_H1' => 'WP-United Installazione Guidata',
	'WP_Wizard_Step' => 'Passo %s di %s.',
	'WP_Wizard_Next' => 'Vai al passo %s -&gt;',
	'WP_Wizard_Back' => '&lt;- Torna al passo %s',
	'WP_Wizard_BackStart' => '&lt;- Torna al primo passo',
	'wizErr_invalid_URL' => "L'URL per Wordpress che hai digitato sembra non essere valido. (Deve essere un URL completo, non relativo). ",
	'wizErr_invalid_Blog_URL' => "L'URL per l'integrazione che hai digitato sembra non essere valido. (Deve essere un URL completo, non relativo). ",
	'wizErr_invalid_Path' =>  "Il percorso che hai digitato sembrano non essere valido. ",

	//Wizard Step 1
	'WP_Wizard_Step1_Title' => 'Installazione in WordPress',
	'WP_Wizard_Step1_Explain1' => 'Assicurati di aver gi&agrave; installato Wordpress.',
	'WP_Wizard_Step1_Explain2' => '&Egrave; possibile installarlo ovunque sullo stesso spazio web del tuo forum phpBB, tranne che all\'interno della directory stessa di phpBB.',
	'WP_Wizard_Step1_Explain2b' => 'Il file fornito, blog.php, sar&agrave; utilizzato per accedere alle pagine di Wordpress integrate (Nel caso in cui si sia scelto di integrare Wordpress in phpBB). &Egrave; possibile anche rinominare il file blog.php e spostarlo dove preferisci (ad esempio, se hai scelto di integrare phpBB in Wordpress &egrave; possibile rinominarlo in index.php e sostituirlo al file index.php originale di Wordpress). Nel caso si scelga di rinominarlo e/o spostarlo, &egrave; necessario aprire il file e indicare il percorso in cui phpBB &egrave; installato.',
	'WP_Wizard_Step1_Explain3' => 'WordPress richiede un database MySQL. &Egrave; possibile utilizzare lo stesso database utilizzato da phpBB, basta assicurarsi che Wordpress e phpBB usino un prefisso diverso per le tabelle.',
	'WP_Wizard_Step1_Explain4' => 'Se si prevede di integrare una installazione di Wordpress pre-esistente, si prega di consultare i nomi utente degli attuali utenti di Wordpress. Una volta che WP-United &egrave; impostato, &egrave; possibile utilizzare l\'apposito strumento "Tool per l\'Integrazione degli utenti", per integrare gli utenti di Wordpress esistenti in phpBB, se necessario.',
	'WP_Wizard_Step1_Explain5' => 'Una volta installato Wordpress, clicca su "Avanti" per continuare. Se si desidera chiudere la procedura guidata, &egrave; possibile riavviare dalla stessa posizione in un secondo momento.',


	//Step Wizard 1B
	'WP_Wizard_Step1b_Title' => 'Posizione dell\'installazione di Wordpress',
	'WP_Wizard_Step1b_Explain1' => 'Abbiamo bisogno di alcuni dettagli per la posizione della tua installazione di Wordpress',

	'WP_Wizard_Step1b_TH1' => 'URL di WordPress',
	'WP_Wizard_URI_Explain' => 'Per favore digita l\'URL completo dell\'installazione di Wordpress. Questo &egrave; l\'URL completo in cui Wordpress &egrave; installato. Devi inserire l\'URL che Wordpress avrebbe se non fosse integrato in phpBB. Ad esempio: http://www.example.com/wordpress/ se Wordpress si trova nella cartella "wordpress" o http://www.example.com/ se Wordpress si trova nella root.',
	'WP_Wizard_URI_Test_Title' => 'Testa URL',
	'WP_Wizard_URI_Test_Explain' => 'Clicca sul bottone a destra per testare che l\'URL che hai inserito sia accessibile.',
	'WP_URI_Test' => 'Testa URL',

	'WP_Wizard_Step1b_TH2' => 'Percorso completo di WordPress',
	'WP_Wizard_Path_Explain1' => 'Abbiamo bisogno del percorso completo di Wordpress. Un esempio di Percorso completo &egrave;: "/home/public/www/wordpress" - ma ogni Host ne usa uno diverso.',
	'WP_Wizard_Path_Explain2' => 'Questa procedura guidata pu&ograve; tentare di rilevare automaticamente il percorso per te, in modo tale da non doverlo digitare. Se tale procedura non riesce, o se preferisci digitarlo tu, puoi inserirlo manualmente.',
	'WP_Path_Test' => 'Rileva il percorso',
	'WP_Wizard_Path_Explain3' => ' o digitalo manualmente qui: ',

	'WP_Wizard_URI_Error' => "ERRORE! Devi inserire un URL valido!",
	'WP_Wizard_Path_Error' => "ERRORE! Devi inserire un percorso valido!",

	'WPWiz_BlogURI_TH' => 'Nuovo indirizzo di integrazione',
	'WPWiz_BlogURI_Title' => 'L\'indirizzo da utilizzare per accedere a Wordpress d\'ora in poi',
	'WPWiz_BlogURI_Explain1' => 'Di default, blog.php, inserito nella cartella principale di phpBB, sar&agrave; utilizzato per accedere alle pagine del tuo Wordpress a partire da adesso.',
	'WPWiz_BlogURI_Explain2' => '&Egrave; possibile anche rinominare il file blog.php e spostarlo dove preferisci (ad esempio, se hai scelto di integrare phpBB in Wordpress &egrave; possibile rinominarlo in index.php e sostituirlo al file index.php originale di Wordpress).',
	'WPWiz_BlogURI_Explain3' => 'Nel caso si scelga di rinominarlo e/o spostarlo, &egrave; necessario aprire il file e indicare il percorso in cui phpBB &egrave; installato.',
	'WPWiz_BlogURI_Explain4' => 'Ricordati per sicurezza di rinominare il vecchio file index.php di Wordpress in index-old.php.',
	'WPWiz_BlogURI_Explain5' => 'Per favore fallo <strong>ora</strong>, e dopo fornisci l\'indirizzo dal quale desideri accedere al tuo Wordpress.',


	'WP_No_JavaScript' => 'NOTA: Questa pagina presenta alcuni miglioramenti che non puoi vedere perch&eacute; il tuo browser &egrave; obsoleto, o perch&eacute; JavaScript &egrave; disattivato.',
	'WP_AJAX_DataError' => 'ERRORE: Impossibile capire la risposta data dal server!',

	//Wizard Step 2
	'WP_Wizard_Step2_Title' => 'Impostazioni di Integrazione',
	'WP_Wizard_Step2_Explain' => 'In questa fase, imposteremo il modo in cui gli utenti accedono in Wordpress',

	'WPWiz_IntLogin_title' => 'Le seguenti opzioni devono essere impostate se si sceglie di integrare i logins...',


	//Wizard Step 3
	'WP_Wizard_Step3_Title' => 'Impostazioni di Visualizzazione e di Comportamento',
	'WP_Wizard_Display_Title' => 'Impostazioni di Visualizzazione',
	'WP_Wizard_Behave_Title' => 'Impostazioni di Comportamento',
	'WP_Wizard_Step3_Explain' => 'Abbiamo bisogno di impostare il modo in cui Wordpress e phpBB appariranno e il loro comportamento.',
	'WPWiz_Inside_Title' => 'Vuoi integrare i template di phpBB e di WordPress?',
	'WPWiz_Template_Forward' => 'Integra WordPress nel template di phpBB',
	'WPWiz_Template_Reverse' => 'Integra phpBB nel template di WordPress',
	'WPWiz_Template_None' => 'Non voglio integrare i loro template',
	'WPWiz_Inside_Explain1' => "WP-United pu&ograve; integrare i template di phpBB e di WordPress.",
	'WPWiz_Inside_Explain2' => "Puoi scegliere di visualizzare WordPress tra l\'header e il footer di phpBB, o visualizzare phpBB tra l\'header e il footer di WordPress, oppure ancora non scegliere nessuna integrazione grafica tra i due. Le opzioni qui di seguito possono variare a seconda del tipo di integrazione che si sceglie.",
	'WPWiz_Template_Forward_Title' => 'Le seguenti opzioni devono essere impostate se scegli di visualizzare WordPress all\'interno di phpBB...',
	'WPWiz_Template_Reverse_Title' => 'Le seguenti opzioni devono essere impostate se scegli di visualizzare phpBB all\'interno di WordPress...',

	'WPWiz_Padding_Title' => 'CSS Padding attorno a phpBB',
	'WPWiz_Padding_Explain1' => 'phpBB &egrave; inserito all\'interno di WordPress mediante l\'uso di un DIV. Qui puoi impostare il padding di tale DIV',
	'WPWiz_Padding_Explain2' => 'Questo &egrave; utile, perch&eacute; altrimenti il contenuto di phpBB potrebbe risultare non allineato correttamente. Le impostazioni predefinite vanno bene per la maggior parte dei temi di Wordpress.',
	'WPWiz_Padding_Explain3' => 'Se preferisci impostarlo tu, lascia queste caselle vuote (non \'0\'), e definisci lo stile per il DIV \'phpbbforum\' nel tuo foglio di stile.',
	'WPWiz_Pixels' => 'pixels',
	'WPWiz_PaddingTop' => 'Sopra',
	'WPWiz_PaddingRight' => 'Destra',
	'WPWiz_PaddingBottom' => 'Sotto',
	'WPWiz_PaddingLeft' => 'Sinistra',

	'WPWiz_WPPage_OptTitle' => 'Se vuoi usare una pagina del template di WordPress, devi impostare le seguenti opzioni...',
	'WPWiz_Page_Title' => 'Seleziona una pagina del template',
	'WPWiz_Page_Explain1' => 'Qui puoi scegliere la pagina del template di WordPress da usare per il tuo forum phpBB. Per esempio, potrebbe essere la pagina del Template dell\'Indice, o la pagina del Template del Singolo Post, o la pagina del Template degli Archivi, o una pagina del Template creata appositamente.',
	'WPWiz_Page_Explain2' => 'Basta digitare il nome della pagina del Template (ES: \'index.php\', \'single.php\' o \'archive.php\') qui. Se WP-United non trova questo file, verr&agrave; utilizzata la pagina del Template \'page.php\'.',



	'WPWiz_CharEnc_Title' => 'Codifica dei Caratteri',
	'WPWiz_CharEnc_Explain1' => 'phpBB2 di default usa la codifica dei caratteri \'iso-8859-1\', mentre WordPress usa di default l\'UTF-8. Pertanto, quando vengono integrati, alcuni caratteri in Wordpress o in phpBB potrebbero non essere visualizzati correttamente.',
	'WPWiz_CharEnc_Explain2' => 'Qui, puoi cambiare la codifica dei caratteri di phpBB con quella usata da WordPress, per le pagine integrate, oppure cambiare la codifica dei caratteri usata da WordPress con quella usata da phpBB. Si consiglia prima di provare con &quot;Cambia la codifica di phpBB&quot; -- se i caratteri non vengono visualizzati correttamente, prova &quot;Cambia la codifica di  WordPress&quot; oppure &quot;Non Cambiare&quot;',
	'WPChar_MatchW' => 'Cambia la codifica di phpBB',
	'WPChar_MatchP' => 'Cambia la codifica di WordPress\'',
	'WPChar_NoChange' => 'Non Cambiare',

	'WPWiz_PStyles_Early_Title' => 'Includi prima lo stile di phpBB?',
	'WPWiz_PStyles_Early_Explain1' => 'Quando i template sono integrati, vengono inseriti i fogli di stile di phpBB e di Wordpress. A volte, alcune definizioni CSS possono andare in conflitto con altre.',
	'WPWiz_PStyles_Early_Explain2' => 'Su una pagina, gli stili che vengono definiti successivamente sovrascrivono quelli sono definiti in precedenza. Quindi impostando l\'ordine di integrazione dei fogli di stile potrebbe essere un modo rapido per risolvere alcuni conflitti.',
	'WPWiz_PStyles_Early_Explain3' => 'Per la maggior parte delle combinazioni dei template, includere il foglio di stile di phpBB prima (cos&igrave; potr&agrave; essere sovrascritto dal foglio di stile di WordPress) &egrave; la migliore scelta, per&ograve;, puoi provare a vedere quale combinazione sia la migliore.',
	'WPWiz_PStyles_Early_Explain4' => 'Se hai intenzione di mettere tutti i tuoi fogli di stile, compreso quello di phpBB, in un unico foglio di stile, puoi disattivare l\'integrazione del foglio di stile di phpBB scegliendo \'Non includere il foglio di stile di phpBB\'.',

	'WPWiz_WPSimple_Title' => 'Integrazione semplice (Header e footer) o vuoi usare una pagina del Template?',
	'WPWiz_WPSimple_Explain1' => 'Vuoi che phpBB appaia semplicemente tra l\'header e il footer del tuo WordPress, o vuoi che venga visualizzato usando una pagina del template di WordPress?',
	'WPWiz_WPSimple_Explain2' => 'L\'Integrazione semplice (Header e footer) funzioner&agrave; meglio per la maggior parte dei temi di Wordpress richiedendo meno modifiche ai fogli di stile.',
	'WPWiz_WPSimple_Explain3' => 'Inoltre, usando una pagina del Template di WordPress per l\'integrazione, potrai far apparire la sidebar, o qualsiasi altra feature di WordPress.',

	'WPWiz_Simple_Yes' => 'Semplice (consigliato)',
	'WPWiz_Simple_No' => 'Usando una pagina del Template',


	'WP_Yes_Recommend' => 'Si (consigliato)',
	'WP_No_Recommend' => 'No (consigliato)',
	'WPWiz_No_PStyles' => 'Non includere il foglio di stile di phpBB',




	'WPWiz_Censor_Title' => 'Usa il Censura Parole di phpBB?',
	'WPWiz_Censor_Explain' => 'Abilita questa opzione se vuoi che i post di WordPress vengano passati attraverso il Censura Parole di phpBB.',


	'WPWiz_Private_Title' => 'Rendi i Blogs Privati?',
	'WPWiz_Private_Explain' => 'Se attivi questa opzione, gli utenti dovranno effettuare il login per visualizzare i blogs. Ti consigliamo di non attivare questa opzione, poich&eacute; WordPress perderebbe molta visibilit&agrave; sui motori di ricerca.',


	//Wizard Step 5
	'WP_Wizard_Connection_Title' => 'Connessione di WP-United',
	'WP_Wizard_Connection_Title2' => 'Sto Installando la Connessione di WP-United...',
	'WP_Wizard_Connection_Explain1' => 'La Connessione di WP-United &egrave; il bridge tra WordPress e phpBB. Essa controlla come si comporta Wordpress quando &egrave; integrato.',
	'WP_Wizard_Connection_Explain2' => 'L\'Installazione Guidata tenter&agrave; ora di installare la Connessione di WP-United...',
	'WP_Wizard_Connection_Success' => 'Installazione avvenuta con successo! La Connessione di WP-United &egrave; stata installata.',
	




	//Wizard Step 4
	'WP_Wizard_Step4_Title' => '&quot;Blog o Non Blog?&quot;',
	'WP_Wizard_Step4_Explain' => 'Hai scelto di integrare i login di phpBB e Wordpress. Se la tua intenzione &egrave; quella di permettere ai membri della tua comunit&agrave; di creare il proprio blog, &egrave; possibile ottimizzare questa scelta con le impostazioni qui di seguito.',
	'WPWiz_OwnBlogs_Title' => 'Concedi agli utenti di avere un proprio blog?',
	'WPWiz_OwnBlogs_Explain1' => 'Se si attiva questa opzione, ciascun membro della tua comunit&agrave; avente il livello di "autore", potr&agrave; creare il proprio blog. Essi saranno in grado di scegliere il titolo, la descrizione, e (opzionalmente), l\'aspetto dei loro blog.',
	'WPWiz_OwnBlogs_Explain2' => 'Attivando questa opzione, probabilmente desidererai effettuare alcune semplici modifiche al tuo tema di Wordpress. Puoi trovare qualche esempio nella cartella "/contrib". Se si desidera usare un tema tra quelli presenti, copiali nella cartella "wp-content/themes/".',
	'WPWiz_BtnsProf_Title' => 'Link del blog nei profili',
	'WPWiz_BtnsProf_Explain' => 'Attivando questa opzione sar&agrave; messo il link "Blog" nei profili degli utenti che hanno un blog attivo.',
	'WPWiz_BtnsPost_Title' => 'Link del blog nei posts',
	'WPWiz_BtnsPost_Explain' => 'Attivando questa opzione sar&agrave; messo il link "Blog" nei post, tra le informazioni dell\'autore del post (accanto ai pulsanti PM, WWW, ecc...), degli utenti che hanno un blog attivo.',
	'WPWiz_StyleSwitch_Title' => 'Gli utenti possono scegliere il loro tema',
	'WPWiz_StyleSwitch_Explain1' => 'Questa opzione da agli utenti che godono dei permessi di "Autore" la possibilit&agrave; di scegliere il tema per il proprio blog.',
	'WPWiz_StyleSwitch_Explain12' => 'Gli utenti possono semplicemente selezionare il tema tra quelli disponibili nella tua installazione di WordPress.',

	'WPWiz_Bloglist_Head' => 'Lista dei Blogs <em>(Solo per WP 2.1 o superiori)</em>',
	'WPWiz_Bloglist_Title' => 'Lista dei Blogs nell\'indice',
	'WPWiz_Bloglist_Explain' => 'Se attivi questa opzione verr&agrave; creata automaticamente una pagina che mostra una lista dei blog degli utenti, con varie informazioni come: avatars, ultimo post, ecc... Se stai usando Wordpress 2.1, questa pagina verr&agrave; automaticamente impostata come home page.',
	'WPWiz_Bloglist_Explain2' => 'Se tu concedi agli utenti di avere un proprio blog, &egrave; consigliato attivare questa opzione.',

	'WPWiz_BlogListHead_Title' => 'Titolo della Blogs Homepage',
	'WPWiz_BlogListHead_Explain' => 'Questo &egrave; il titolo della Blogs Homepage.',
	'WPWiz_BlogListHead_Default' => 'Blogs Homepage',
				
	'WPWiz_BlogIntro_Title' => 'Introduzione della Blogs Homepage',
	'WPWiz_BlogIntro_Explain' => 'Questo &egrave; il testo di introduzione da visualizzare nelle homepage dei tuoi blog.',
	'WPWiz_BlogIntro_Explain2' => 'Il tag {GET-STARTED} sar&agrave; sostituito da un link che incoragger&agrave; le persone a registrarsi, effettuare il login e creare il loro blog.',
	'WPWiz_NumBlogList_Title' => 'Numero di Blogs da mostrare in ogni pagina',
	'WPWiz_NumBlogList_Explain' => 'Qui potrai impostare il numero dei blogs da visualizzare in ogni pagina della lista',
	'WPWiz_LatestPosts_Title' => 'Mostrare anche la lista degli ultimi post?',
	'WPWiz_LatestPosts_Explain' => 'Qui puoi impostare se vuoi che la lista dei post di Wordpress appaia sotto alla lista dei blog. Impostando il valore \'0\' disabiliterai la lista dei post. Impostare un numero maggiore di \'0\' per impostare il numero dei post da mostrare.',
	'WPWiz_blCSS_Title' => 'Utilizza il CSS di WP-United per lo stile della lista',
	'WPWiz_blCSS_Explain' => 'Lascia attiva questa opzione per usare il CSS di WP-United previsto per la lista dei blogs. Il CSS &egrave; incluso nel file \'wpu-blog-homepage.css\', che potete trovare nella cartella \'root\wp-united\theme\' di WP-United.',
	'WPWiz_blCSS_Explain2' => 'Si consiglia di lasciare attiva questa opzione inizialmente. Tuttavia, una volta che sarai soddisfatto dello stile CSS della lista, probabilmente vorrai copiare tale codice CSS del file wpu-blog-homepage.css nel CSS principale del tuo tema. Una volta fatto ci&ograve;, sar&agrave; possibile disattivare questa opzione per migliorare le prestazioni del tuo sito.',
	'WPWiz_blogIntro_Default' => 'Benvenuto sui nostri blogs! Qui, i membri della comunit&agrave; possono creare il proprio blog. {GET-STARTED} oppure, puoi semplicemente visitare i blogs dei nostri membri qui di seguito:',


	'WP_OwnBlogs_OptTitle' => 'Le seguenti opzioni devono essere impostate se concedi agli utenti di avere un proprio blog...',
	'WP_Bloglist_OptTitle' => 'Le seguenti opzioni devono essere impostate se vuoi usare la lista dei blogs...',


	//Phew! Wizard End
	'Wizard_Success1' => 'Hai completato l\'Installazione Guidata con successo! Ora puoi accedere alla tua integrazione cliccando %squi%s.',
	'Wizard_Success2' => '&egrave; possibile modificare queste impostazioni in qualsiasi momento, visitando la pagina "Impostazioni" dal Pannello di Amministrazione.',

	//Other strings that get returned from functions
	'WP_URI_Found' => 'Test superato! Una pagina &egrave; stata trovata a questo indirizzo',
	'WP_cURL_Not_Avail' => 'ERRORE: Questo test richiede le librerie cURL, che non sono disponibili sul tuo server. Questo test non pu&ograve; essere eseguito. Tuttavia, se sei sicuro che l\'impostazione sia corretta, puoi procedere.',
	'WP_URI_Not_Found' => 'ERRORE: Impossibile effettuare la connessione all\'URL (&egrave; stato restituito un errore 404). Per favore, verifica prima se hai digitato l\'URL  correttamente, e se esiste una pagina a questo indirizzo. Se sei sicuro che l\'impostazione sia corretta, procedi, ma probabilmente sar&agrave; necessario prima risolvere il problema.',
	'WP_URI_OK_Diff_Host' => 'ATTENZIONE: Una pagina &egrave; stata trovata in questa posizione, ma si trova su un altro dominio. Puoi procedere ugualmente, ma il rilevamento automatico del percorso non funzioner&agrave;, e l\'integrazione potrebbe produrre risultati indesiderati. Si consiglia di correggere il problema prima di continuare.', 
	'WP_URI_No_Diff_Host' => 'ERRORE: Impossibile effettuare la connessione all\'URL. Inoltre, il nome del dominio non corrisponde al nome del dominio corrente, che &egrave; altamente sconsigliato. Per favore verifica d\'aver digitato correttamente l\'URL, e se esiste una pagina a questo indirizzo. Si consiglia di correggere il problema prima di continuare.',

	'WP_PathTest_Diff_Host' => 'ERRORE: Il percorso non pu&ograve; essere individuato perch&eacute; l\'URL che hai digitato si trova in un dominio diverso. Si prega di correggere l\'errore o di digitare il percorso manualmente.',
	'WP_PathTest_Invalid_URL' => 'ERRORE: Il percorso non pu&ograve; essere rilevato in quanto non hai inserito un URL sopra, o esso non &egrave; valido',
	'WP_PathTest_Not_Detected' => 'ERRORE: Impossibile rilevare il percorso automaticamente. Si prega di digitarlo manualmente, e cliccare sul pulsante \'Testa\' per verificarlo.',
	'WP_PathTest_Success' => '&Egrave; stata rilevata un\'installazione di WordPress in %s',
	'WP_PathTest_GuessedOnly' => 'ATTENZIONE: La procedura guidata suggerisce il percorso "%s" - tuttavia in esso non &egrave; stata trovata un\'installazione di Wordpress. Puoi continuare, ma l\'integrazione non funzioner&agrave;. Per favore, controlla di aver prima installato Wordpress, o digita il percorso manualmente.',
	'WP_PathTest_TestOnly_NotFound' => 'ERRORE: Non &egrave; stata trovata un\'installazione di Wordpress in questo percorso. Puoi continuare, ma l\'integrazione non funzioner&agrave;. Per favore, controlla di aver prima installato Wordpress, o digita il percorso manualmente.',

	'WP_Wizard_Complete_Title' => 'Installazione Guidata Completata!',
	'WP_Wizard_Complete_Explain0' => 'Congratulazioni, l\'installazione &egrave; completa! Per le opzioni di integrazione avanzate, come il caching e la compressione, modifica il file options.php che trovi nella cartella di WP-United.',
	'WP_Wizard_Complete_Explain1' => 'Se hai gi&agrave; attivato i permalink di Wordpress, aggiorna la struttura dei Permalink dal pannello di amministrazione di Wordpress.',
	'WP_Wizard_Complete_Explain2' =>'In questo modo tutti i link punteranno a Wordpress in maniera corretta.',
	'WP_Wizard_Complete_Explain3' => 'Grazie per aver installato WP-United. Se &egrave; di tuo gradimento ti invitiamo a sostenerci con una donazione! Ogni somma, per quanto piccola, &egrave; sempre molto apprezzata!',


	//USER MAPPING STRINGS
	'L_MAP_TITLE' 	=> 	'Gestore dell\'Integrazione degli Utenti di WP-United',
	'L_MAP_INTRO1' 	=>	'Questo tool consente di integrare gli utenti di Wordpress in specifici utenti di phpBB.',
	'L_MAP_INTRO2'	=>	'Cosa far&agrave; questo tool:',
	'L_MAP_INTRO3'	=>	'Lo script legger&agrave; la lista dei vostri utenti di Wordpress. Se non sono integrati, il tool cercher&agrave; di trovare un utente phpBB con username corrispondente, sul presupposto che, probabilmente si desidera integrare tali utenti.',
	'L_MAP_INTRO4' 	=> 	'Potrai scegliere se integrare questo utente, o digitare il nome di un altro utente di phpBB. In alternativa, &egrave; possibile non integrare questo utente di Wordpress. Puoi anche decidere se cancellare l\'utente da Wordpress o creare un nuovo utente corrispondente in phpBB.',
	'L_MAP_INTRO5' 	=> 	'Se l\'utente &egrave; gi&agrave; integrato ad un utente di phpBB, potrai decidere se rimuovere l\'integrazione, o lasciarla.',
	'L_MAP_INTRO6' 	=> 	'NOTA: Prima di eseguire questo tool, &egrave; CONSIGLIATO eseguire il backup dei database di Wordpress e di phpBB.',
	'L_MAP_INTRO7' 	=> 	'Clicca su &quot;Inizia&quot; per iniziare.',
	'L_COL_WP_DETAILS'	=>	'Dettagli di WordPress',
	'L_COL_MATCHED_DETAILS'	=>	'Dettagli Trovati/Suggeriti di phpBB',
	'L_USERID'	=>	'User ID',
	'L_USERNAME'	=>	'Username',
	'L_NICENAME'	=>	'\'Nicename\'',
	'L_NUMPOSTS'	=>	'N&deg; dei Posts',
	'L_MAP_STATUS'	=>	'Stato',
	'L_MAP_ACTION'	=>	'Azione',

	'L_MAPMAIN_1'		=>	'A sinistra, sono elencati gli utenti del tuo Wordpress. A destra, &egrave; indicato lo stato di ciascun utente (integrato o non integrato). Se l\'utente non &egrave; integrato, ma &egrave; stato trovato un utente uguale esso verr&agrave; mostrato. Se non &egrave; quello giusto, &egrave; possibile digitare un nome utente diverso. Se non si trova nessuna corrispondenza, puoi non integrarli o creare un utente in phpBB (di default).',
	'L_MAPMAIN_2'		=>	'Sulla destra, selezionate un\'azione adeguata per ciascun utente (di default vengono gi&agrave; selezionate le scelte per noi migliori). Quindi, clicca su \'Procedi\'. NOTA: Avrete la possibilit&agrave; di confermare ogni azione nella fase successiva.',
	'L_MAPMAIN_MULTI' => 	'In alternativa, &egrave; possibile cliccare su \'Salta e vai alla Pagina Successiva\' per saltare gli utenti della pagina attuale e passare agli utenti della pagina successiva.',
	'L_MAP_BEGIN' 	=> 	'Inizia',   
	'L_MAP_NEXTPAGE' 	=> 	'Pagina Successiva',       
	'L_MAP_SKIPNEXT' 	=> 	'Salta e vai alla Pagina Successiva',      
	    
	    
	    
	'L_MAP_ERROR_MULTIACCTS' =>   ' ERRORE: Integrato con pi&ugrave; di un account!',
	'L_MAP_BRK' => 'Rimuovi Integrazione',
 	'L_MAP_BRK_MULTI' => 'Rimuovi Integrazioni',
	'L_MAP_NOT_INTEGRATED' => 'Non Integrare',
	'L_MAP_INTEGRATE' => 'Integra',
	'L_MAP_ALREADYINT' => 'Gi&agrave; Integrato',
	'L_MAP_LEAVE_INT' => 'Lascia Integrato',
	'L_MAP_CREATEP' => 'Crea Utente in phpBB',
	'L_MAP_CANTCONNECTP' => 'Non &egrave; possibile connettersi al database di phpBB',
    'L_MAP_LEAVE_UNINT' => 'Lascia Non Integrato',
    'L_MAP_UNINT_FOUND' => 'Non Integrato (account da suggerire trovato)',
    'L_MAP_UNINT_FOUNDBUT' => 'Non Integrato (\'%1s\' account trovato, ma esso (%2s) &egrave; gi&agrave; integrato con l\'account utente di WordPress (ID account: %3s))',
    'L_MAP_UNINT_NOTFOUND' => 'Non Integrato (nessun account da suggerire trovato)', 
    'L_MAP_ERROR_BLANK' => 'Errore: campo vuoto',
    'L_MAP_DEL_FROM_WP' => 'Cancella da WP',
    'L_MAP_PROCESS' => 'Procedi',
    'L_MAP_NOUSERS' => 'Nessun account utente di WordPress &egrave;  stato trovato &ndash; quindi questo tool non pu&ograve; esserti utile!',
    
    'L_MAP_CANT_CONNECT' => 'ERRORE: Non &egrave; possibile effettuare la connessione con WordPress!',
    'L_WP_NO_SETTINGS' => 'Impossibile collegarsi alle Impostazioni di WP-United',
    'L_COL_WP_DETAILS' => 'Dettagli di WordPress',
    'L_COL_MATCHED_DETAILS' => 'Dettagli Trovati/Suggeriti di phpBB',
    'L_USERID' => 'ID Utente',
	'L_USERNAME' => 'Username',
	'L_NICENAME' => '\'Nicename\'',
	'L_NUMPOSTS' => 'N&deg; dei Posts',
	'L_USERNAME' => 'username',
	'L_USERID' => 'ID Utente',
	'L_MAP_STATUS' => 'Stato',
	'L_MAP_ACTION' => 'Azione',
	
	
	'L_MAP_ACTIONSINTRO' => 'Le tue selezioni produrranno le seguenti azioni:',
 	'L_MAP_ACTIONSEXPLAIN1' => 'Se una di queste ti sembra errata, clicca sul pulsante \'indietro\' del tuo browser e procedi alla correzione. Una volta effettuate le eventuali correzioni cliccare su \'Esegui le Azioni\' per eseguire le azioni definite.',
	'L_MAP_NOWTTODO' => 'Non ci sono Azioni da eseguire. Clicca sul pulsante \'indietro\' del tuo browser e seleziona qualche azione o clicca su \'Salta e vai alla pagina successiva\'.',
	'L_MAP_ERR_GOBACK' => 'Non tutte le azioni possono essere eseguite. Per favore clicca sul pulsante \'indietro\' del tuo browser e procedi alla correzione.',
	
	'L_MAP_BREAKWITH' => 'Rimuovi l\'integrazione con l\'utente \'%s\' di phpBB',
	'L_MAP_INTWITH' => 'Integra con l\'utente \'%s\' di phpBB',
	'L_MAP_BREAKEXISTING' => 'Rimuovi l\'integrazione esistente',
	'L_MAP_BREAKMULTI' => 'Rimuovi le integrazioni esistenti',
	'L_MAP_DEL_WP' => 'Cancella l\'utente da WordPress',
	'L_MAP_CREATE_P' => 'Crea l\'utente in phpBB',
	'L_MAP_PNOTEXIST' => 'ERRORE: Questo utente di phpBB non esiste!',
	'L_MAP_ERR_ALREADYINT' => 'ERRORE: Questo utente di phpBB &egrave; gi&agrave; integrato!',
	
	'L_PROCESS_ACTIONS' => 'Esegui le Azioni',
	
	'L_MAP_PERFORM_INTRO' => 'Le seguenti azioni sono state adottate:',
	
	'L_MAP_COULDNT_BREAK' => 'Impossibile rimuovere l\'integrazione',
	'L_DB_ERROR' => 'Database error',
	'L_MAP_BROKE_SUCCESS' => 'Integrazione rimossa con successo per l\'ID utente \'%s\' di WordPress.',
	'L_MAP_CANNOT_BREAK' => 'Errore: Impossibile rimuovere l\'integrazione: utente WordPress non specificato!',
	'L_MAP_COULDNT_INT' => 'Impossibile integrare',
	'L_MAP_INT_SUCCESS' => 'Integrati: L\'utente WordPress %1s <-> L\'utente phpBB %2s',
	'L_MAP_CANNOT_INT' => 'Errore: Impossibile integrare gli utenti, ID mancante!',
	'L_MAP_WPDEL_SUCCESS' => 'Utenti WordPress Cancellati %s',
	'L_MAP_CANNOT_DEL' => 'ERRORE: Impossibile cancellare l\'utente di WordPress, ID mancante',
	'L_MAP_CANNOT_CREATEP_ID' => 'ERRORE: Impossibile creare un utente phpBB, l\'username o l\'ID di WordPress risulta mancante!',
	'L_MAP_CREATEP_SUCCESS' => 'Utente phpBB \'%s\' creato! (NOTA: L\'UTENTE NON &Egrave; ANCORA INTEGRATO IN WORDPRESS, ESEGUI NUOVAMENTE IL TOOL PER INTEGRARLO.)',
	'L_MAP_CANNOT_CREATEP_NAME' => 'ERRORE: Impossibile creare l\'account utente in phpBB (username potrebbe non essere valido, o l\'username/e-mail potrebbe gi&agrave; esistere!)',
	'L_MAP_INVALID_ACTION' => 'ERRORE &ndash; Azione non valida #%s',
	'L_MAP_INVALID_ACTION' => 'ERRORE &ndash; Azione vuota #%s',
	'L_MAP_FINISHED' => 'L\'Integrazione degli utenti &egrave; terminata! Clicca %1squi%2s per tornare alla pagina principale di WP-United, oppure clicca %3squi%4s per eseguire nuovamente il tool o per controllare i cambiamenti.',
	
	'WP_Reset' => 'Resetta WP-United',
	'WP_Reset_Button' => 'Resetta',
	'WP_Reset_Explain' => 'Resettando WP-United i moduli del Pannello di Controllo di WP-United torneranno al loro stato originale &ndash; ci&ograve; pu&ograve; essere utile se le hai cambiate e vuoi che vengano ripristinate le impostazioni predefinite. WP-United apparir&agrave; come \'disinstallato\' fino a quando non rieseguirai l\'Installazione Guidata. Le impostazioni di WordPress, l\'integrazione degli utenti, e i permessi di WP-United rimarranno intatti e NON saranno alterati.<br /><br /> La maggior parte delle persone non hanno bisogno di usare questa opzione &ndash; usala SOLO se sei sicuro di voler perdere le impostazioni di WP-United!',
	'WP_Did_Reset' => 'Reset riuscito correttamente!',
	'WP_Reset_Confirm' => 'Sei sicuro di voler resettare le impostazioni di WP-United?',
	'WP_Reset_Log' => 'Reset WP-United settings to initial state',

	'WP_Uninstall' => 'Disinstalla WP-United',
	'WP_Uninstall_Button' => 'Disinstalla',
	'WP_Uninstall_Explain' => 'Disinstallando WP-United si rimuoveranno TUTTI gli aspetti della mod, da WordPress e phpBB, tranne i files che sono stati editati per eseguire la mod e i file che sono stati copiati che non devi rimuovere necessariamente. Tutti i dati dell\'integrazione degli utenti andranno persi -- se gli account dei tuoi utenti di phpBB erano integrati in WordPress, essi continueranno ad esistere, ma non saranno integrati con quelli di phpBB, e potranno essere reintegrati solo se reinstallerai WP-United e li reintegrerai manualmente utilizzando l\'apposito tool.<br /><br /> Il programma di disinstallazione prover&agrave; a rimuovere da Wordpress tutte le impostazioni di WP-United, incluse le opzioni dei singoli utenti. <br /><br />La maggior parte delle persone non hanno bisogno di usare questa opzione &ndash; usala SOLO se sei sicuro di voler perdere TUTTE le impostazioni di WP-United! Si consiglia di eseguire il backup dei dati prima di continuare!',
	'WP_Uninstall_Confirm' => 'Sei sicuro di voler disinstallare WP-United?',		
	'WP_Uninstall_Log' => 'WP-United Rimosso Completamente',
	
	//new in v0.5.5
	'L_INFO_TO_POST' => 'Informazioni utili da postare quando richiedi aiuto',
	'WP_Debug' => 'Informazioni per il Debug',
	'WP_Debug_Explain' => 'Se hai problemi con WP-United, e hai bisogno d\'aiuto sul forum newsitewpunited.com, potrebbero essere d\'aiuto, a chi vorr&agrave; aiutarti, le informazioni per il Debug. <br /><br />Ti consigliamo inoltre di pubblicare per intero il testo di qualsiasi errore riscontrato o ulteriori informazioni di debug. Se hai dei problemi con l\'integrazione degli utenti, attiva il debug dal file \'wp-united/options.php\'. <br /> <br /> NOTA: Ti consigliamo di nascondere le informazioni sul percorso quando posti delle informazioni riguardanti il tuo sito.',
	'DEBUG_SETTINGS_SECTION' => 'Impostazioni di WP-United:',
	'DEBUG_PHPBB_SECTION' => 'Impostazioni di phpBB:',
	
	//new in v0.6
	'WP_XPost_Title' => 'Permetti il cross-posting dei posts del blog nel forum?',
	'WP_XPost_Explain' => 'Se attivi questa opzione, gli utenti a cui sar&agrave; permesso di avere un proprio blog potranno copiare il post che stanno postando sul loro blog nel forum con pochi click. Per impostare in quali forum gli utenti potranno effettuare un "cross-post", visita il pannello dei permessi di phpBB, e abilita il permesso al cross-posting per gli utenti e/o per i gruppi che desideri.',
	'WP_XPost_OptTitle' => '&Egrave; possibile impostare la seguente opzione se hai scelto di integrare i login',
	
	//New in v0.7.0 -- please use the WPWiz prefix, phpbb_smilies is too common and will collide with other mods
	'WPWiz_Fix_Header_Title' => 'Rimuovi l\'header di phpBB?',
	'WPWiz_Fix_Header_Explain1' => 'Se attivi questa opzione l\'header di phpBB sar&agrave; rimosso. Questa opzione funzioner&agrave; con i temi Prosilver, subSilver2 e derivati. Se vuoi usare un tema personalizzato o vuoi editare il tema tu stesso, disattiva questa opzione. Altrimenti lasciala attiva per una facile integrazione di phpBB in WordPress.',
	'WPWiz_Fix_Header_Explain2' => 'WP-United will try to automatically position the phpBB Quick Search box in the WordPress header. If it does not appear, or you want to put it somewhere else, add the tag &lt;!--PHPBB_SEARCH--&gt; to your WordPress template, and it will automatically appear there.',
	'WPWiz_Fix' => 'Rimuovi',
	'WPWiz_No_Fix' => 'Non rimuovere',
	'WPWiz_phpBB_Smilies_Title' => 'Vuoi usare gli smilies di phpBB in Wordpress?',
	'WPWiz_phpBB_Smilies_Explain' => 'Attiva questa opzione se vuoi usare gli smilies di phpBB nei commenti e nei post di WordPress.',

	'WP_Wizard_Connection_Fail' => 'Errore durante l\'installazione di WP-United Connection!',
	'WPWizard_Connection_Fail_Explain1' => 'WP-United Connection non pu&ograve; essere installata. Ci&ograve; &egrave; probabilmente dovuto ad uno dei seguenti essere impostati in modo errato: (a) percorso di Wordpress non valido, (b)percorso dello script impostato per phpBB nelle configurazioni della board non valido. Si prega di correggere questi e riprovare.',
	'WPWizard_Connection_Fail_Explain2' => 'Il file [phpbb]/wp-united/wpu-plugin.php non pu&ograve; essere copiato nella cartella dei plugins di WordPress. Per favore copialo manualmente, o rendi scrivibile la cartella dei plugins di WordPress. Dopo la copia, si dovrebbe avere una copia del file wpu-plugin.php in [phpbb]/wp-united e una copia nella tua cartella dei plugins di WordPress. Quando ci&ograve; &egrave;  stato fatto, aggiorni questa pagina per rieseguire la procedura guidata.',
	'WPU_Conn_InstallError' => 'ERRORE: WP-United Plugin non &egrave; stato trovato nella cartella dei plugins di Wordpress. Per favore copia il file wp-united/wpu-plugin.php l&igrave; ora, e riprova!<br />',
	'WPU_Cache_Unwritable' => 'ATTENZIONE: La cartella [phpbb]/wp-united/cache non &egrave; scrivibile. Per ottenere prestazioni migliori, dovresti rendere questa cartella scrivibile prima di procedere.',
	'WPU_Install_Exists' => 'ATTENZIONE: Il file wpu-install.php si trova ancora nella root di phpBB. Dopo aver eseguito questo file, DEVI cancellarlo prima di continuare.',

	'Map_Items_PerPage' => 'Oggetti da mostrare per ogni pagina',
	'Map_Change_PerPage' => 'Modifica',
	'Map_Quick_Actions' => 'Selezione rapida',
	'Map_Delete_All_Unintegrated' => 'Cancella tutti i non integrati',
	'Map_Break_All' => 'Rimuovi tutte le integrazioni',
	'Map_Reset_Default' => 'Resetta selezionati',
	'DEBUG_SERVER_SETTINGS' => 'Server:',
));

?>