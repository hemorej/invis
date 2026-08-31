$CREATE_RELEASE()

cd $FORGE_RELEASE_DIRECTORY
git clone --depth 1 --branch=$FORGE_SITE_BRANCH $REPO_URL
git submodule update --init --recursive

cd $FORGE_RELEASE_DIRECTORY/site/config && ln -s /mnt/$VOLUME_NAME/site/config/config.php .
cd $FORGE_RELEASE_DIRECTORY/site/config && ln -s /mnt/$VOLUME_NAME/site/config/config.php config.the-invisible-cities.ca.php
cd $FORGE_RELEASE_DIRECTORY/site/config && ln -s /mnt/$VOLUME_NAME/site/config/.license .

cd $FORGE_RELEASE_DIRECTORY/content && rm -rf 1_projects && ln -s /mnt/$VOLUME_NAME/content/1_projects .
cd $FORGE_RELEASE_DIRECTORY/content && rm -rf 2_prints && ln -s /mnt/$VOLUME_NAME/content/2_prints .
cd $FORGE_RELEASE_DIRECTORY/content && rm -rf 3_journal && ln -s /mnt/$VOLUME_NAME/content/3_journal .
cd $FORGE_RELEASE_DIRECTORY/content && rm -rf journal-series && ln -s /mnt/$VOLUME_NAME/content/journal-series .
cd $FORGE_RELEASE_DIRECTORY/content && rm -rf travels && ln -s /mnt/$VOLUME_NAME/content/travels .
cd $FORGE_RELEASE_DIRECTORY/content/4_about && ln -s /mnt/$VOLUME_NAME/content/4_about/j_by_manu.jpg .

cd $FORGE_RELEASE_DIRECTORY/assets && rm -rf font && ln -s /mnt/$VOLUME_NAME/assets/font .

cd $FORGE_RELEASE_DIRECTORY && ln -s /mnt/$VOLUME_NAME/media .
cd $FORGE_RELEASE_DIRECTORY/site && ln -s /mnt/$VOLUME_NAME/site/accounts .

$PNPM_PATH install --frozen-lockfile && $PNPM_PATH run prod

$ACTIVATE_RELEASE()

sudo service php8.5-fpm reload