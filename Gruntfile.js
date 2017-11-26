module.exports = function(grunt) {
  grunt.initConfig({
    // JS linting
    jshint: {
      options: grunt.file.readJSON('.jshintrc'),
      files: ['Gruntfile.js', 'assets/*.js', '!assets/*.min.js']
    },

    // minify JS files
    uglify: {
      options: {
        ASCIIOnly: true,
        screwIE8: false
      },
      main: {
        files: {
          'assets/wp-healthcheck.min.js': ['assets/*.js', '!assets/*.min.js']
        }
      }
    },

    // minify CSS files
    cssmin: {
      main: {
        files: [{
          expand: true,
          src: ['assets/*.css', '!assets/*.min.css'],
          ext: '.min.css'
        }]
      }
    },

    // build zip file
    compress: {
      main: {
        options: {
          archive: 'wp-healthcheck.zip'
        },
        files: [{
          expand: true,
          src: [
            'assets/**',
            'languages/**',
            'includes/**',
            'views/**',
            'LICENSE',
            'readme.txt',
            'index.php',
            'wp-healthcheck.php'
          ],
          dest: 'wp-healthcheck/'
        }]
      }
    },

    // watch changes for assets
    watch: {
      src: {
        files: ['assets/*.css', 'assets/*.js', '!assets/*.min.css', '!assets/*.min.js'],
        tasks: ['default']
      }
    },

    // i18n: verify if translation functions were used correctly
    checktextdomain: {
      options: {
        text_domain: 'wp-healthcheck',
        correct_domain: true,
        keywords: [
          '__:1,2d',
          '_e:1,2d',
          '_x:1,2c,3d',
          'esc_html__:1,2d',
          'esc_html_e:1,2d',
          'esc_html_x:1,2c,3d',
          'esc_attr__:1,2d',
          'esc_attr_e:1,2d',
          'esc_attr_x:1,2c,3d',
          '_ex:1,2c,3d',
          '_n:1,2,4d',
          '_nx:1,2,4c,5d',
          '_n_noop:1,2,3d',
          '_nx_noop:1,2,3c,4d'
        ]
      },
      files: {
        src: ['**/*.php', '!vendor/**', '!node_modules/**'],
        expand: true
      }
    },

    // i18n: generate POT file
    makepot: {
      main: {
        options: {
          domainPath: 'languages',
          potFilename: 'wp-healthcheck.pot',
          potHeaders: {
            'report-msgid-bugs-to': 'https://wordpress.org/support/plugin/wp-healthcheck'
          },
          processPot: function(pot) {
            var excluded_meta = ['Plugin Name of the plugin/theme', 'Plugin URI of the plugin/theme', 'Author of the plugin/theme', 'Author URI of the plugin/theme'];

            var translation;

            for (translation in pot.translations['']) {
              var comment = pot.translations[''][translation].comments;

              if ('extracted' in comment && comment.extracted !== 'undefined') {
                if (excluded_meta.indexOf(comment.extracted) >= 0) {
                  delete pot.translations[''][translation];
                }
              }
            }

            return pot;
          },
          type: 'wp-plugin'
        }
      }
    },

    // run shell commands
    shell: {
      phpcs: {
        command: 'vendor/bin/phpcs'
      }
    }
  });

  grunt.loadNpmTasks('grunt-contrib-compress');
  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-checktextdomain');
  grunt.loadNpmTasks('grunt-shell');
  grunt.loadNpmTasks('grunt-wp-i18n');

  grunt.registerTask('default', ['jshint', 'shell:phpcs', 'uglify:main', 'cssmin:main']);
  grunt.registerTask('build', ['default', 'compress:main']);
  grunt.registerTask('i18n', ['checktextdomain', 'makepot:main']);
};
