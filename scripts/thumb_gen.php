<?php

// RUNTIME: place at top of blade

$pages = array('about', 'projects/second-floor-window', 'projects/color', 'projects/lag-baomer', 'projects/montreal', 'projects/landwehrkanal', 'projects/hokkaido', 'projects/a-summer-melancholy', 'projects/portfolio');

// $pages = array('process/starting-projects', 'process/contacts-work-prints', 'process/editing', 'process/hurdles-and-challenges', 'process/project-end', 'process/technical-notes');

// $pages = array('travels/new-york', 'travels/berlin', 'travels/paris', 'travels/japan', 'travels/peru', 'travels/los-angeles');
// $pages = array('travels/prague', 'travels/vancouver', 'travels/stockholm', 'travels/buenos-aires', 'travels/frankfurt', 'travels/iceland', 'travels/ottawa');

// $pages = array('journal/01-03-2019', 'journal/01-10-2019', 'journal/01-23-2019', 'journal/01-29-2019', 'journal/02-05-2019', 'journal/02-11-2019', 'journal/02-15-2019', 'journal/02-18-2019', 'journal/03-01-2019', 'journal/03-05-2019', 'journal/03-08-2019', 'journal/03-21-2019', 'journal/04-07-2019', 'journal/04-19-2019', 'journal/04-28-2019', 'journal/05-12-2019', 'journal/05-25-2019', 'journal/06-12-2019', 'journal/06-17-2019', 'journal/07-14-2019', 'journal/07-18-2019', 'journal/07-22-2019', 'journal/12-15-2019', 'journal/12-16-2019', 'journal/12-18-2019', 'journal/12-20-2019', 'journal/12-22-2019', 'journal/01-30-2020', 'journal/01-31-2020', 'journal/02-01-2020', 'journal/02-02-2020', 'journal/02-03-2020', 'journal/02-04-2020', 'journal/02-05-2020', 'journal/02-06-2020', 'journal/02-07-2020', 'journal/02-08-2020', 'journal/02-09-2020', 'journal/02-10-2020', 'journal/02-12-2020', 'journal/02-13-2020', 'journal/02-15-2020', 'journal/02-25-2020', 'journal/02-27-2020', 'journal/02-28-2020', 'journal/03-07-2020', 'journal/03-08-2020', 'journal/03-09-2020', 'journal/03-10-2020', 'journal/03-11-2020', 'journal/03-12-2020', 'journal/03-13-2020', 'journal/03-14-2020', 'journal/03-17-2020', 'journal/03-18-2020', 'journal/03-19-2020', 'journal/03-23-2020', 'journal/03-25-2020', 'journal/03-30-2020', 'journal/04-01-2020', 'journal/04-03-2020', 'journal/04-08-2020', 'journal/04-13-2020', 'journal/04-21-2020', 'journal/05-01-2020', 'journal/05-17-2020', 'journal/05-21-2020', 'journal/06-04-2020', 'journal/06-10-2020', 'journal/06-15-2020', 'journal/06-20-2020', 'journal/06-26-2020', 'journal/07-04-2020', 'journal/07-06-2020', 'journal/07-15-2020', 'journal/07-17-2020', 'journal/07-23-2020', 'journal/07-24-2020', 'journal/07-25-2020', 'journal/07-26-2020', 'journal/07-27-2020', 'journal/07-28-2020', 'journal/07-29-2020', 'journal/07-30-2020', 'journal/07-31-2020', 'journal/08-01-2020', 'journal/08-02-2020', 'journal/08-03-2020', 'journal/08-04-2020', 'journal/08-05-2020', 'journal/08-06-2020', 'journal/08-07-2020', 'journal/08-08-2020', 'journal/08-09-2020', 'journal/08-10-2020', 'journal/08-11-2020', 'journal/08-12-2020', 'journal/08-13-2020', 'journal/08-28-2020', 'journal/08-29-2020', 'journal/08-30-2020', 'journal/08-31-2020', 'journal/09-01-2020', 'journal/09-02-2020', 'journal/09-03-2020', 'journal/09-04-2020', 'journal/09-10-2020', 'journal/09-20-2020', 'journal/09-21-2020', 'journal/09-24-2020', 'journal/09-25-2020', 'journal/09-27-2020', 'journal/09-29-2020', 'journal/10-1-2020', 'journal/10-02-2020', 'journal/10-05-2020', 'journal/10-08-2020', 'journal/10-16-2020', 'journal/10-23-2020', 'journal/10-25-2020', 'journal/10-27-2020', 'journal/11-01-2020');

foreach($pages as $child){
    $page = $site->page($child);
    
    if(empty($page) || empty($page->images()))
        continue;

    foreach($page->images() as $file){
        if($file->isResizable()) {
            if($page->title() != 'About' && $page->parent()->title() == 'Shop'){
                $file->resize(100, 100)->save();
            }

            if($file->isPortrait()){
                $file->resize(null, 600)->save();
                $file->resize(null, 500)->save();
            }else{
                $file->resize(600)->save();
                $file->resize(800)->save();
                $file->resize(1200)->save();
            }

            $file->resize(38, 38)->save();
            $file->resize(76, 76)->save();
        }
    }
}

?>