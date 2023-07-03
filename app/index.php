<?php


error_reporting(E_ALL);
ini_set('display_errors', 'on');

require('_.php');
$twig = new \tip\twig;
$cms = new \tip\cosmic;
$page_data = [];
$output_path = \tip\conf::output_path;

$page_data['projects'] = $cms->fetch_projects();
$page_data['articles'] = $cms->fetch_articles();


$path = $_REQUEST['path']??'index.html';
$article_slug = $_REQUEST['article']??false;
if($article_slug) {
    $index = array_search( $article_slug, array_column( array_values($page_data['articles']), 'slug') );
    $values = array_values($page_data['articles']);
    $article = $values[$index];
    $page_data['article'] = $article;
}
$twig->put("template/" . $path, $page_data);

exit;




exit;
foreach($page_data['projects'] as $project) {
    // render project pages.
    //file_put_contents( 'output/project/' . $project->slug . '.html', $twig->render('render/project.html', ['project'=>$project]) );
}

foreach($page_data['articles'] as $article) {
    // render article pages.
    file_put_contents( $output_path . '/article/' . $article->slug . '.html', $twig->render('render/article.html', ['article'=>$article]) );
}

// render article list
file_put_contents( $output_path . '/article/index.html', $twig->render('render/article-list.html', $page_data) );


// output homepage
file_put_contents( $output_path . '/index.html', $twig->render('render/index.html', $page_data) );

// create about page
file_put_contents( $output_path . '/about.html', file_get_contents($output_path . '/article/chief-freelance-officer-matthew-knight.html'));

print "done";
