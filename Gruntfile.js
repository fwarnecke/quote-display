/**
 * Created by h.veerkamp on 15.05.17.
 */

module.exports = function(grunt) {

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        sass: {
            dist: {
                files: {
                    'css/main.css': ['src/scss/main.scss']
                }
            }
        },


        concat: {
            options: {
                separator: ';'
            },
            dist: {
                src: ['src/js/*.js'],
                dest: 'js/<%= pkg.name %>.js'
            }
        },


        cssmin: {
            target: {
                files: [{
                    src: ['css/main.css'],
                    dest: 'css/style.css'
                }]
            }
        }


    });


    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-sass');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.registerTask('default', ['concat', 'sass']);

};



