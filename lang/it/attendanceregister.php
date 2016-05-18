<?PHP

$string['modulename'] = 'Registro Presenze';
$string['modulenameplural'] = 'Registro Presenze';
$string['modulename_help'] = 'Il Registro Presenze calcola il tempo passato dal Partecipante
    all\'intero dei Corsi.<br />
    Opzionalmente consente al Partecipante di registrare attività effettuate "offline",
    cioè al di fuori del sito Moodle.<br />
    A seconda della <b>Modalità di Tracciamento Presenza</b>, il Registro traccia
    le attività svolte in un singolo Corso, in tutti i Corsi di una Categoria
    o in tutti i Corsi con un <i>Collegamento Meta Corso</i> con il Corso dove si
    trova il Registro.<br />
    Le Sessioni di lavoro online sono calcolate automaticamente dal Registro,
    basandosi sul Log delle Attività di Moodle.<br />
    <b>Le nuove Sessioni online vengono aggiunte con un certo ritardo.</b> Il calcolo
    viene aggiornato periodicamente dal Cron e comunque una Sessione viene aggiunta
    solo dopo che l\'utente ha effettuato Logout (o è scaduto il Timeout di Sessione).';
$string['pluginname'] = 'Registro Presenze';
$string['pluginadministration'] = 'Amministrazione Registro Presenze';

// Mod instance form
$string['registername'] = 'Nome Registro Presenze';
$string['registertype'] = 'Modalità di Tracciamento Presenza';
$string['registertype_help'] = 'La Modalità di Tracciamento Presenza determina
    in quali Corsi viene tracciata l\'attività dei Partecipanti.
* _Solo questo Corso_: solo il Corso in cui si trova il Registro.
* _Tutti i Corsi nella stessa Categoria_: viene tracciata l\'attività in questo Corso più tutti quelli appartenti alla stessa Categoria
* _Tutti i Corsi con Collegamento Meta Corso_: viene tracciata l\'attività in questo Corso e in tutti quelli con un Meta-collegamento (Meta-Corso pre 2.0)';
$string['sessiontimeout'] = 'Timeout di Sessione';
$string['sessiontimeout_help'] = 'Il Timeout di Sessione è utilizato per stimare la durata delle Sessioni Online.<br />
    Le Sessioni Online saranno almeno lunghe <b>meta</b> del tempo di Timeout.<br />
    Se impostato ad un tempo troppo lungo, il Registro tenderà a sovrastimare la durata delle Sessioni Online.<br />
    Se troppo corso, sessioni di lavoro reali saranno spezzate dal Registro in più Sessioni, corte.<br />
    <h3>Spiegazione lunga</h3>
    Le Sessioni di lavoro Online vengono <b>stimate</b> sulla base delle voci nel Log
    dell\'Utente, per attività all\'interno dei Corsi tracciati dal Registro (vedi <i>Modalità di Tracciamento Presenza</i>).<br />
    Se tra due registrazioni di Log successive passa meno del Timeout di Sessione,
    il Registro considera che l\'utente abbia continuato a lavorare online (quindi la Sessione presegue).<br />
    Se tra una registrazione e l\'altra passa più del Timeout di Sessione, il Registro
    considera che l\'Utente si sia scollegato <b>metà</b> del Timeout di Sessione dopo la prima registrazione
    (quindi la Sessione viene terminata) e si sia ricollegato alla successiva registrazione
    (quindi viene inizata una nuova Sessione).';
$string['offline_sessions_certification'] = 'Sessioni offline (autocertificazione)';
$string['enable_offline_sessions_certification'] = 'Abilita Sessioni offline';
$string['offline_sessions_certification_help'] = 'Abilità la possibilità per l\'Utente di
    registrare (auto-certificare) delle sessioni di lavoro al di fuori del sito.<br />
    Questa registrazione è utile se - per ragioni "burocratiche" - il Partecipante
    deve mantenere un registro di tutte le attività di studio.<br />
    Notare che solo gli Utenti reali possono inserire Sessioni Offline: Amministratori
    in "Login come..." un altro utente NON POSSONO farlo!' ;
$string['dayscertificable'] = 'Massimo numero di giorni precedenti auto-certificabile';
$string['dayscertificable_help'] = 'Limita quanti giorni indietro può essere retrodatata
    un\'auto-certificazione di Sessione Offline';
$string['offlinecomments'] = 'Commenti';
$string['offlinecomments_help'] = 'Abilita la possibilità di inserire commenti/descrizioni aggiuntive dell\'attività offline';
$string['mandatory_offline_sessions_comments'] = 'Commenti obbligatori';
$string['offlinespecifycourse'] = 'Specificare il Corso di riferimento';
$string['offlinespecifycourse_help'] = 'Consente di selezionare un Corso di riferimento per un\'attività offline.<br />
    E\' signifiativo solo se il Registro traccia più Corsi (cioè in Modo: "Categoria" o "Meta-Collegamento"), altrimenti non ha senso';
$string['mandatoryofflinespecifycourse'] = 'Corso di riferimento obbligatorio';
$string['mandatoryofflinespecifycourse_help'] = 'Rende obbligatorio specificare il Corso di riferimento nell\'attività offline';


$string['type_course'] = 'Solo questo Corso';
$string['type_category'] = 'Tutti i Corsi nella stessa Categoria';
$string['type_meta'] = 'Tutti i Corsi con Collegamento Meta Corso';

$string['maynotaddselfcertforother'] = 'Non ti è permesso inserire Sessioni offline per conto di altri Utenti.';
$string['onlyrealusercanaddofflinesessions'] = 'Solo gli utenti _reali_ possono inserire Sessioni offline (no "Login come...")';
$string['onlyrealusercandeleteofflinesessions'] = 'Solo gli utenti _reali_ possono cancellare Sessioni offline (no "Login come...")';

// Capabilities
$string['attendanceregister:tracked'] = 'L\'attività dell\'Utente viene tracciata dal Registro Presenze';
$string['attendanceregister:viewownregister'] = 'Può visualizzare il proprio Registro Presenze';
$string['attendanceregister:viewotherregisters'] = 'Può visualizzare i Registri Presenze di altri';
$string['attendanceregister:addownofflinesess'] = 'Può aggiungere Sessioni Offline al proprio Registro Presenze';
$string['attendanceregister:addotherofflinesess'] = "Può aggiungere Sessioni Offline al al Registro Presenze di altri Utenti";
$string['attendanceregister:deleteownofflinesess'] = 'Può cancellare Sessioni Offline dal proprio Registro Presenze';
$string['attendanceregister:deleteotherofflinesess'] = 'Può cancellare Sessioni Offline dal Resigistro Presenze di altri Utenti';
$string['attendanceregister:recalcsessions'] = 'Può forzare il Ricalcolo delle Sessioni online del Registro Presenze';
$string['attendanceregister:addinstance'] = "Aggiungere Registri Presenze";

// Buttons & Links labels
$string['force_recalc_user_session'] = 'Forza Ricalcolo Sessioni online di questo Utente';
$string['force_recalc_all_session'] = 'Forza Ricalcolo delle Sessioni online di tutti gli Utenti';
$string['force_recalc_all_session_now'] = 'Ricalcola Sessioni, ora';
$string['schedule_reclalc_all_session'] = 'Ricalcolo Sessioni al prossimo Cron';
$string['recalc_scheduled_on_next_cron'] = 'Il Ricalcolo Sessioni è programmato per il prossimo Cron';
$string['recalc_already_pending'] = '(Già programmato per il prossimo Cron)';
$string['first_calc_at_next_cron_run'] = 'Eventuali Sessioni passate appariranno al prossimo Cron';
$string['back_to_tracked_user_list'] = 'Torna alla lista degli Utenti';
$string['recalc_complete'] = 'Ricalcolo Sessioni online completato';
$string['recalc_scheduled'] = 'Il ricalcolo Sessioni è stato programmato. Verrà eseguito al prossimo Cron';
$string['offline_session_deleted'] = 'La Sessione offline è stata eliminata';
$string['offline_session_saved'] = 'La nuova Sessione offline è stata salvata';
$string['show_printable'] = 'Versione Stampabile';
$string['show_my_sessions'] = 'Le mie Sessioni';
$string['back_to_normal'] = 'Torna alla versione normale';
$string['force_recalc_user_session_help'] = 'Cancellla e ricalcola tutte le Sessioni online di questo Utente.<br />
    Normalmente <b>non è necessario ricalcolare</b> le sessioni!<br />
    Le nuove Sessioni vengono automaticamente calcolate a aggiunte dopo un certo tempo.<br />
    Il ricacolo è necessario <b>solamente</b> in questi casi:
    <ul>
      <li>Dopo aver modificato il Ruolo di questo Utente e l\'utente aveva precedentemente partecipato a questo Corso (o ad un altro dei Corsi tracciati
      dal Registro), ma con un ruolo differente; p.e. passa da Docente (non tracciato) a Studente (tracciato)</li>
      <li>Dopo aver modificato le impostazioni del Registro che influenzano il calcolo delle Sessioni
      (<i>Modalità di Tracciamento presenza</i> e <i>Timeout di Sessione</i>)</li>
    </ul>';
$string['force_recalc_all_session_help'] = 'Cancella e ricalcola tutte le Sessioni online di tutti gli Utenti tracciati.<br />
    Normalmente <b>non è necessario ricalcolare</b> le sessioni!<br />
    Le nuove Sessioni vengono automaticamente calcolate a aggiunte dopo un certo tempo.<br />
    Il ricacolo è necessario <b>solamente</b> in questi casi:
    <ul>
      <li>Dopo aver modificato il Ruolo di un Utente che aveva precedentemente partecipato a questo Corso (o ad un altro dei Corsi tracciati
      dal Registro), ma con un ruolo differente; p.e. passa da Docente (non tracciato) a Studente (tracciato)</li>
      <li>Dopo aver modificato le impostazioni del Registro che influenzano il calcolo delle Sessioni
      (<i>Modalità di Tracciamento presenza</i> e <i>Timeout di Sessione</i>)</li>
    </ul>
    Il ricalcolo <b>non è necessario quando si iscrive un nuovo partecipante</b>!<br /><br />
    L\'esecuzione può essere immediata oppure programmata.<br />
    Il ricalcolo programmato (consigliabile per corsi molto affollati)
    viene eseguito automaticamente alla prossima esecuzione del Cron.';

// Table columns
$string['count'] = '#';
$string['start'] = 'Inizio';
$string['end'] = 'Fine';
$string['duration'] = 'Durata';
$string['online_offline'] = 'Online/Offline';
$string['ref_course'] = 'Corso di rif.';
$string['comments'] = 'Commenti';
$string['fullname'] = 'Nome';
$string['click_for_detail'] = 'click per visualizzare i dettagli';
$string['total_time_online'] = 'Totale tempo Online';
$string['total_time_offline'] = 'Totale tempo Offline';
$string['grandtotal_time'] = 'Totale generale';

$string['online'] = 'Online';
$string['offline'] = 'Offline';
$string['not_specified'] = '(non spec.)';
$string['never'] = '(mai)';
$string['session_added_by_another_user'] = 'Aggiunta da: {$a}';
$string['unknown'] = '(sconosciuto)';

$string['are_you_sure_to_delete_offline_session'] = 'Sei sicuro di voler cancellare questa Sessione?';
$string['online_session_updated'] = "Sessioni online aggiornate";
$string['updating_online_sessions_of'] = 'Aggiornamento sessioni online di {$a}';
$string['online_session_updated_report'] = 'Sessioni online di {$a->fullname} aggiornate: trovate {$a->numnewsessions} nuove';

$string['user_sessions_summary'] = 'Riepilogo Utente';
$string['online_sessions_total_duration'] = 'Totale Sessioni online';
$string['offline_refcourse_duration'] = 'Totale offline, per Corso:';
$string['no_refcourse'] = '(Corso non spec.)';
$string['offline_sessions_total_duration'] = 'Totale sessioni offline';
$string['sessions_grandtotal_duration'] = 'Totale generale';
$string['last_session_logout'] = 'Fine ultima/attuale sessione online';
$string['last_calc_online_session_logout'] = 'Ultima sessione online tracciata dal Registro (escl. Sessione corrente)';
$string['last_site_login'] = 'Ultimo Login al Sito';
$string['prev_site_login'] = 'Precedente Login al Sito';
$string['last_site_access'] = 'Ultima attività registrata nel Sito';

$string['no_session_for_this_user'] = '- Non è ancora stata registrata nessuna Sessione per questo Utente -';
$string['no_tracked_user'] = '- Questo Registro Presenze non traccia nessun Utente -';
$string['no_session'] = 'Nessuna Sessione';

$string['tracked_courses'] = 'Corsi tracciati';
$string['duration_hh_mm'] = '{$a->hours} h, {$a->minutes} min';
$string['duration_mm'] = '{$a->minutes} min';

// Offline Session form
$string['select_a_course_if_any'] = '- Selezionare un Corso (opz.) -';
$string['select_a_course'] = '- Selezionare un Corso -';
$string['insert_new_offline_session'] = 'Inserisci una nuova Sessione Offline';
$string['insert_new_offline_session_for_another_user'] = 'Inserisci una nuova Sessione Offline per {$a->fullname}';
//$string['offline_session_form_explain'] = 'You may enter an offline session of work.<br/>
//    The offline work time will be added to the online sessions automatically recorded by the Attendance Register.<br/>
//    The new session may not overlap with any existing work session, either online or offline, nor it may be more than {$a->dayscertificable} days ago.<br/>
//    You may delete any offline session later.';
$string['offline_session_start'] = 'Inizio';
$string['offline_session_start_help'] = 'Selezionare Data e Ora di Inizio e di Fine della Sessione offline.<br />
    Non può sovrapporsi con nessuna Sessione precedente, sia online che offline, né con la Sessione online attuale (dall\'ultimo Login)';
$string['offline_session_end'] = 'Fine';
$string['offline_session_comments'] = 'Commenti/Note';
$string['offline_session_comments_help'] = 'Descrivi il lavoro svolto o l\'argomento di studio.';
$string['offline_session_ref_course'] = 'Corso di riferimento';
$string['offline_session_ref_course_help'] = 'Seleziona un Corso al quale si riferisce il lavoro svolto offline';

// Offline Sessions validations
$string['login_must_be_before_logout'] = 'L\'Inizio della Sessione non può essere dopo la Fine';
$string['dayscertificable_exceeded'] = 'Non può risalire a più di {$a} giorni fa';
$string['overlaps_old_sessions'] = 'Non può sovrapporsi con altre sessioni registrate, né online né offline';
$string['overlaps_current_session'] = 'Non può sovrapporsi con la sessione attuale (dall\'ultimo Login)';
$string['unreasoneable_session'] = 'Sei sicuro? Sono più di {$a} ore!';
$string['logout_is_future'] = 'Non può essere futura';

$string['tracked_users'] = 'Utenti tracciati';

// Activity Completion tracking
$string['completiontotalduration'] = 'Tempo minimo [minuti]';
$string['completiondurationgroup'] = 'Tempo totale tracciato';

// Log
$string['user_attendance_details_viewed'] = 'Visti dettagli di partecipazione singolo utente';
$string['participants_attendance_report_viewed'] = 'Visto report generale partecipazione studenti';
$string['user_attendance_deloffline'] = 'Cancellazione dichiarazione di partecipazione offline';
$string['user_attendance_addoffline'] = 'Aggiunta dichiarazione di partecipazione offline';
$string['mod_attendance_recalculation'] = 'Ricalcolo dei log per aggiornamento sessoni';

// Cron
$string['crontask']='Ricalcolo sessioni attendanceregister';

// Alert
$string['standardlog_disabled'] = 'Moodle Standard Log è disabilitato. Tutte le nuove sessioni degli utenti non sono tracciate';
$string['standardlog_readonly'] = 'Moodle Standard Log è sola lettura. Tutte le nuove sessioni degli utenti non sono tracciate';
