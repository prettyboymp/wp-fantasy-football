/*
 * Fantasy Football
 * https://github.com/voceconnect/fantasy-football
 */

'use strict';

module.exports = function (grunt) {

  // Project configuration.
  grunt.initConfig({
    "jshint": {
      "options": {
        "curly": true,
        "eqeqeq": true,
        "eqnull": true,
        "browser": true,
        "plusplus": true,
        "undef": true,
        "unused": true,
        "trailing": true,
        "globals": {
          "jQuery": true,
          "$": true,
          "ajaxurl": true
        }
      },
      "theme": [
        "js/main.js"
      ],
    },
    "uglify": {
      "theme": {
        "options": {
          "preserveComments": "some"
        },
        "files": {
          "js/main.min.js": [
            "js/main.js",
            "js/skip-link-focus-fix.js"
          ],
          "js/libs/bootstrap.min.js": [
            "js/libs/bootstrap/**/*.js",
            "!js/libs/bootstrap/popover.js",
            "js/libs/bootstrap/popover.js"
          ]
        }
      }
    },
    "react": {
      "theme": {
        "files": {
          "js/components.js": [
            "js/react-components/*.jsx"
          ]
        }
      }
    },
    "concat": {
      "bootstrap": {
        "src": [
          "js/libs/bootstrap/**/*.js",
          "!js/libs/bootstrap/popover.js",
          "js/libs/bootstrap/popover.js"
        ],
        "dest": "js/libs/bootstrap.js"
      },
      "main": {
        "src": [
          "js/main.js",
          "js/skip-link-focus-fix.js"
        ],
        "dest": "js/main.min.js"
      }
    },
    "imagemin": {
      "theme": {
        "files": [
          {
            "expand": true,
            "cwd": "img/",
            "src": "**/*.{png,jpg}",
            "dest": "img/"
          }
        ]
      }
    },
    "compass": {
      "options": {
        "config": "config.rb",
        "basePath": "",
        "force": true
      },
      "production": {
        "options": {
          "environment": "production"
        }
      },
      "development": {
        "options": {
          "environment": "development"
        }
      }
    },
    "autoprefixer": {
      "multiple_files": {
        "src": "*.css"
      }
    },
    "watch": {
      "scripts": {
        "files": "js/**/*.js",
        "tasks": ["concat"]
      },
      "images": {
        "files": "img/**/*.{png,jpg,gif}",
        "tasks": ["imagemin"]
      },
      "composer": {
        "files": "composer.json",
        "tasks": ["composer:update"]
      },
      "styles": {
        "files": "sass/**/*.scss",
        "tasks": ["compass:development", "autoprefixer"]
      },
      "react": {
        "files": "js/react-components/*.jsx",
        "tasks": ["react"]
      },
    },
    "build": {
      "production": ["react", "uglify", "compass:production", "autoprefixer"],
      "uat": ["react", "uglify", "compass:production", "autoprefixer"],
      "staging": ["react", "concat", "compass:development", "autoprefixer"],
      "development": ["react", "concat", "compass:development", "autoprefixer"]
    }
  });

  //load the tasks
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-react');
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-imagemin');
  grunt.loadNpmTasks('grunt-contrib-compass');
  grunt.loadNpmTasks('grunt-autoprefixer');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-autoprefixer');
  grunt.loadNpmTasks('grunt-peon-build');

  //set the default task as the development build
  grunt.registerTask('default', ['build:development']);

};
