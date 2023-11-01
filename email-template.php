<?php
//the data is come from generateLink.php page
$url = home_url("/feedback/?token={$value->token}&bcaId={$value->bcaId}");
?>
<table border="0" width="100%" cellspacing="0" cellpadding="0">
	<tbody>
		<tr>
			<td align="center"><img src="https://sogertspa.it/wp-content/uploads/logo-sogert_350x103.jpg" alt="Logo"
					height="100" /></td>
		</tr>
	</tbody>
</table>
Ciao <?= $value->full_name ?>,

Speriamo che tu abbia apprezzato la tua recente prenotazione con noi. Per favore, condividi la tua esperienza con una
recensione.

<strong>Dettagli della Prenotazione:</strong>
<ul>
	<li><strong>Data:</strong> <?= date('m-d-Y', strtotime($value->start_date)) ?></li>
	<li><strong>Ora:</strong> <?= date('H:i', strtotime($value->start_date)) ?></li>

	<li><strong>Servizio:</strong> <?= $value->category ?></li>
	<li><strong>Comuna:</strong> <?= $value->comune ?></li>
</ul>
Fai clic sul seguente pulsante per aggiungere la tua recensione:

<a href="<?= $url ?>">
	<button
		style="background-color: #4caf50; border: none; color: white; padding: 10px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; margin: 4px 2px; cursor: pointer;">Aggiungi
		una Recensione</button>
</a>

La tua opinione è importante per noi e ci aiuta a migliorare i nostri servizi. Grazie in anticipo per il tuo feedback.

Saluti,