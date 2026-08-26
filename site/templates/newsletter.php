<?php snippet('partials/header') ?>
<?php snippet('partials/menu') ?>

<div class="mb-22">
	<span class="spectral f-23 fw5 ink-dark ttl">Newsletter</span>
	<div class="nl-rule" style="max-width:1040px"></div>
</div>

<?php if(!empty($notice)): ?>
	<p class="spectral f-18 lh-16 <?= $notice['type'] === 'error' ? 'gold' : 'ink-copy' ?> ba b--accent pa3 mb-32" style="max-width:520px">
		<?= html($notice['message']) ?>
	</p>
<?php endif ?>

<div class="nl-grid mt-32">

	<!-- row 1: eyebrows -->
	<span class="gold-lbl">— subscribe</span>
	<span class="gold-lbl">— unsubscribe</span>

	<!-- row 2: lede copy -->
	<div class="spectral nl-lede">
		<?php if(!empty(page()->intro()->value())): ?>
			<?= kirbytext(page()->intro()) ?>
		<?php else: ?>
			Occasional notes on new work, prints and journal entries. No spam, unsubscribe any time.
		<?php endif ?>
	</div>
	<div class="spectral nl-lede">
		Already on the list and want off? Enter the address you subscribed with.
	</div>

	<!-- row 3: field + button -->
	<form action="<?= html(url('newsletter/subscribe')) ?>" method="post" class="nl-field-row">
		<input type="hidden" name="csrf" value="<?= csrf() ?>">
		<div style="position:absolute;left:-9999px;top:-9999px;" aria-hidden="true">
			<label for="nl-website">Leave this field empty</label>
			<input type="text" id="nl-website" name="website" tabindex="-1" autocomplete="off">
		</div>
		<label class="visually-hidden" for="nl-email">email</label>
		<input id="nl-email" name="email" type="email" required placeholder="you@example.com" class="nl-input spectral">
		<button type="submit" class="nl-btn nl-btn-primary spectral">subscribe</button>
	</form>

	<form action="<?= html(url('newsletter/unsubscribe')) ?>" method="post" class="nl-field-row">
		<input type="hidden" name="csrf" value="<?= csrf() ?>">
		<div style="position:absolute;left:-9999px;top:-9999px;" aria-hidden="true">
			<label for="nl-unsub-website">Leave this field empty</label>
			<input type="text" id="nl-unsub-website" name="website" tabindex="-1" autocomplete="off">
		</div>
		<label class="visually-hidden" for="nl-unsub-email">email</label>
		<input id="nl-unsub-email" name="email" type="email" required placeholder="you@example.com" class="nl-input spectral">
		<button type="submit" class="nl-btn nl-btn-secondary spectral">unsubscribe</button>
	</form>

	<!-- row 4: notes -->
	<p class="spectral nl-note">
		Roughly one edition a month.
		<a class="lnk-muted" href="<?= html(url('newsletter/editions')) ?>">see what's already been sent &raquo;</a>
	</p>

</div>

<?php snippet('partials/footer') ?>
