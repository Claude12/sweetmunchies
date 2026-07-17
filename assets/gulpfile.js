const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const sourcemaps = require('gulp-sourcemaps');
const babel = require('gulp-babel');
const postcss = require('gulp-postcss');
const autoprefixer = require('autoprefixer');
const cssnano = require('cssnano');
const webpack = require('webpack-stream');
const ESLintPlugin = require('eslint-webpack-plugin');
const browserSync = require('browser-sync').create();

// CSS task
const css = () => {
  return gulp
    .src('scss/**/*.scss')
    .pipe(sourcemaps.init())
    .pipe(sass({ errLogToConsole: true }))
    .pipe(postcss([autoprefixer, cssnano]))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('../dist/css/'))
    .pipe(browserSync.stream());
};

// JS task
const js = () => {
  return gulp
    .src('js/main.js')
    .pipe(
      babel({
        presets: ['@babel/env'],
      })
    )
    .pipe(
      webpack({
        mode: 'production',
        devtool: 'source-map',
        externals: {
          jquery: 'jQuery',
        },
        plugins: [new ESLintPlugin()],
      })
    )
    .pipe(gulp.dest('../dist/js/'))
    .pipe(browserSync.stream());
};

// Watch task
const watchFiles = () => {
  browserSync.init({
    proxy: 'http://localhost/sweetmunchies_wordpresscms/', // Replace with your local site URL
    open: false, // Prevent the browser from automatically opening
  });

  gulp.watch('scss/**/*.scss', css);
  gulp.watch('js/**/*.js', js).on('change', browserSync.reload); // Reload for JS changes
  gulp.watch('../**/*.php').on('change', browserSync.reload); // Reload for PHP changes
};

// Exports
exports.watch = gulp.series(gulp.parallel(css, js), watchFiles);
exports.build = gulp.parallel(css, js);
