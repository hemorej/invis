module.exports = function (grunt) {
 
    grunt.initConfig({
        convert: {
            options: {
              explicitArray: false,
            },
            xml2json: {
                files: [
                  {
                    expand: true,
                    cwd: 'grunt',
                    src: ['sitemap.xml'],
                    dest: 'grunt',
                    ext: '.json'
                  }
                ]
            },
        },
        uncss: {
          dist: {
            options: {
              ignore: ['.contain-to-grid', '.alert-box', '.top-bar']
            }
          },
        },
        cssmin: {
            dist: {
                files: [
                    { src: 'assets/css/tidy.css', dest: 'assets/css/tidy.min.css' }
                ]
            }
        }
    });
 
    // Load the plugins
    grunt.loadNpmTasks('grunt-uncss');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-convert');
 
    // Default tasks.
    grunt.registerTask('sitemap', ['convert']);
    grunt.registerTask('all', ['load_sitemap', 'uncss', 'cssmin']);
    grunt.registerTask('load_sitemap', function() {
        var sitemap = grunt.file.readJSON('sitemap.json');
        var urls = [];
        sitemap.urlset.url.forEach(function(each){
            urls.push(each.loc);
        });
        grunt.config.set('uncss.dist.files', [{'nonull': true, 'src' : urls, 'dest': 'assets/css/tidy.css'}]);
    });
 
};