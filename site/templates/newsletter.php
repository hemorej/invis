<?php snippet('partials/header') ?>
<?php snippet('partials/menu') ?>

<div class="mb-34">
	<span class="spectral f-23 fw5 ink-dark ttl">Newsletter</span>
</div>

<?php if(!empty($notice)): ?>
	<p class="spectral f-18 lh-16 <?= $notice['type'] === 'error' ? 'gold' : 'ink-copy' ?> ba b--accent pa3 mb-32" style="max-width:520px">
		<?= html($notice['message']) ?>
	</p>
<?php endif ?>

<div class="flex flex-wrap" style="gap:64px;">
	<div style="flex:1;min-width:300px;max-width:56ch">
		<?php if(!empty(page()->intro()->value())): ?>
			<p class="spectral f-18 lh-17 ink-copy mb-32"><?= kirbytext(page()->intro()) ?></p>
		<?php endif ?>

		<form action="<?= html(url('newsletter/subscribe')) ?>" method="post">
			<input type="hidden" name="csrf" value="<?= csrf() ?>">
			<div style="position:absolute;left:-9999px;top:-9999px;" aria-hidden="true">
				<label for="nl-website">Leave this field empty</label>
				<input type="text" id="nl-website" name="website" tabindex="-1" autocomplete="off">
			</div>

			<label class="spectral f-15 lh-1 ink-subtle db mb2" for="nl-email">email</label>
			<div class="flex flex-wrap" style="gap:12px;">
				<input id="nl-email" name="email" type="email" required placeholder="you@example.com" class="cart-input spectral" style="flex:1;min-width:220px;">
				<button type="submit" class="btn-cart spectral">subscribe</button>
			</div>
		</form>

		<a class="lnk-muted spectral f-16 db mt-28" href="<?= html(url('newsletter/editions')) ?>">see what's already been sent &raquo;</a>
	</div>

	<div style="flex:0 0 260px;min-width:220px;">
		<span class="gold-lbl mb-22">— unsubscribe</span>
		<p class="spectral f-15 lh-17 ink-subtle mb-16">Already on the list and want off? Enter your email below.</p>
		<form action="<?= html(url('newsletter/unsubscribe')) ?>" method="post">
			<input type="hidden" name="csrf" value="<?= csrf() ?>">
			<div style="position:absolute;left:-9999px;top:-9999px;" aria-hidden="true">
				<label for="nl-unsub-website">Leave this field empty</label>
				<input type="text" id="nl-unsub-website" name="website" tabindex="-1" autocomplete="off">
			</div>

			<label class="spectral f-15 lh-1 ink-subtle db mb2" for="nl-unsub-email">email</label>
			<input id="nl-unsub-email" name="email" type="email" required placeholder="you@example.com" class="cart-input spectral db mb-16">
			<button type="submit" class="btn-cart spectral">unsubscribe</button>
		</form>
	</div>
</div>

<?php snippet('partials/footer') ?>
