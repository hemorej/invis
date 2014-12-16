<?php

echo page('journal')->children()->visible()->flip()->limit(10)->feed();