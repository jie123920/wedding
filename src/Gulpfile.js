var gulp = require('gulp');
var browserSync = require('browser-sync').create();
var compass = require('gulp-compass');
var plumber = require('gulp-plumber');
//var inject = require('gulp-inject');
var watch = require('gulp-watch');
var uncss = require('gulp-uncss');
var concat = require('gulp-concat');
var minifyCss = require('gulp-minify-css');
var rev = require('gulp-rev');
var revReplace = require('gulp-rev-replace');
var processhtml = require('gulp-processhtml');
var rimraf = require('rimraf');
var gulpSequence = require('gulp-sequence').use(gulp);
var jsmin = require('gulp-jsmin');
var fs = require('fs');
var makedir = require('makedir');
var htmlmin = require('gulp-htmlmin');

gulp.task('default', function () {
    console.log(1111);
});

//Public/static
gulp.task('build-static', function() {
    return gulp.src(['./web/Public/src/static/**'])
        .pipe(rev())
        .pipe(gulp.dest('./web/Public/dist/static'))
        .pipe(rev.manifest())
        .pipe(gulp.dest('./dist/rev/static'));

});

var minjs = [
    './web/Public/src/wedding/js/jquery-1.9.1.min.js',
    './web/Public/src/wedding/js/respond.src.js',
    './web/Public/src/wedding/js/shop1.js',
    './web/Public/src/wedding/js/TouchSlide.1.1.js',
    './web/Public/src/wedding/js/jquery.SuperSlide.2.1.1.js',
    // './web/Public/src/wedding/js/layer/layer.js',
    './web/Public/src/wedding/js/jquery.form.js',
    './web/Public/src/wedding/js/jquery.validate.min.js',
    './web/Public/src/static/jquery.lazyload.min.js',
    './web/Public/src/wedding/js/jquery.cookie.js'
]
gulp.task("minjs",function(){
    return gulp.src(minjs)
        .pipe(concat('main.js'))
        .pipe(rev())
        .pipe(gulp.dest('./web/Public/dist/wedding/js/'))
        .pipe(rev.manifest())
        .pipe(gulp.dest('./dist/rev/wedding/js'));
})

//--------------------wedding----------------------------
gulp.task('build-wedding-font', function() {
    return gulp.src('./web/Public/src/wedding/fonts/**')
        .pipe(rev())
        .pipe(gulp.dest('./web/Public/dist/wedding/fonts/'))
        .pipe(rev.manifest())
        .pipe(gulp.dest('./dist/rev/wedding/fonts'));
});
gulp.task('build-wedding-images', function() {
    return gulp.src(['./web/Public/src/cf/images/**'])
        .pipe(rev())
        .pipe(gulp.dest('./web/Public/dist/wedding/images'))
        .pipe(rev.manifest())
        .pipe(gulp.dest('./dist/rev/wedding/images'));
});
gulp.task('build-wedding-css', function() {
    return gulp.src(['./web/Public/src/wedding/css/**'])
        .pipe(minifyCss())
        .pipe(rev())
        .pipe(gulp.dest('./web/Public/dist/wedding/css/'))
        .pipe(rev.manifest())
        .pipe(gulp.dest('./dist/rev/wedding/css'));
});
gulp.task('build-wedding-js', function() {
    return gulp.src('./web/Public/src/wedding/js/**')
        .pipe(rev())
        .pipe(gulp.dest('./web/Public/dist/wedding/js/'))
        .pipe(rev.manifest())
        .pipe(gulp.dest('./dist/rev/wedding/js'));
});
gulp.task('build-replace-css-wedding', function() {
    var manifest = gulp.src("./dist/rev/wedding/**/*.json");
    return gulp.src(["./web/Public/dist/wedding/css/*.css"])
        .pipe(revReplace({
            manifest: manifest
        }))
        .pipe(gulp.dest('./web/Public/dist/wedding/css'));
});
gulp.task('build-replace-wedding-lang', function() {
    var manifest = gulp.src("./dist/rev/wedding/images/*.json");
    return gulp.src(["./modules/wedding/languages/src/**/*.php"])
        .pipe(revReplace({
            manifest: manifest,
            replaceInExtensions:['.js', '.css', '.html', '.php']
        }))
        .pipe(gulp.dest('./modules/wedding/languages'));
});

gulp.task('build-replace-font-wedding', function() {
    var manifest = gulp.src("./dist/rev/wedding/**/*.json");
    return gulp.src(["./web/Public/dist/wedding/fonts/**/*.*"])
        .pipe(revReplace({
            manifest: manifest
        }))
        .pipe(gulp.dest('./web/Public/dist/wedding/fonts'));
});
gulp.task('build-replace-views-wedding', function() {
    var options = {
        removeComments: true,//清除HTML注释
        //collapseWhitespace: true,//压缩HTML
        //collapseBooleanAttributes: true,//省略布尔属性的值 <input checked="true"/> ==> <input />
        //removeEmptyAttributes: true,//删除所有空格作属性值 <input id="" /> ==> <input />
        //removeScriptTypeAttributes: true,//删除<script>的type="text/javascript"
        //removeStyleLinkTypeAttributes: true,//删除<style>和<link>的type="text/css"
        //minifyJS: true,//压缩页面JS
        //minifyCSS: true//压缩页面CSS
    };

    var manifest = gulp.src("./dist/rev/wedding/**/*.json");
    return gulp.src(["./modules/wedding/views/src/**/*.html"])
        .pipe(revReplace({
            manifest: manifest
        }))
        .pipe(htmlmin(options))
        .pipe(gulp.dest('./modules/wedding/views/dist'));
})


gulp.task('build-wedding', gulp.series('build-wedding-font','build-wedding-images','build-wedding-css','build-wedding-js'));
gulp.task('build-replace-wedding', gulp.series('build-replace-font-wedding','build-replace-css-wedding','build-replace-views-wedding','build-replace-wedding-lang'));
//---------------------wedding-------------------------------------


gulp.task('build-clean-dist', function(callback) {
    return rimraf('./dist', callback);
});

gulp.task('build-clean-rev', function(callback) {
    return rimraf('./dist/rev', callback);
});


gulp.task('build-clean-view', function(callback) {
    return rimraf('./modules/**/view/dist', callback);
});

gulp.task('build-clean-lang', function(callback) {
    return rimraf('./modules/**/languages/*.php', callback);
});

// 清空生产环境文件
gulp.task('build-clean', gulp.series('build-clean-rev', 'build-clean-dist','build-clean-view','build-clean-lang'));


gulp.task('build', gulp.series(
    'build-clean',
    //wedding
    'build-wedding',
    'build-replace-wedding'
));


//TEST
gulp.task('test', function() {
    var options = {
        removeComments: true,//清除HTML注释
        collapseWhitespace: true,//压缩HTML
        collapseBooleanAttributes: true,//省略布尔属性的值 <input checked="true"/> ==> <input />
        removeEmptyAttributes: true,//删除所有空格作属性值 <input id="" /> ==> <input />
        removeScriptTypeAttributes: true,//删除<script>的type="text/javascript"
        removeStyleLinkTypeAttributes: true,//删除<style>和<link>的type="text/css"
        //minifyJS: true,//压缩页面JS
        //minifyCSS: true//压缩页面CSS
    };

    var manifest = gulp.src("./dist/rev/wedding/**/*.json");
    return gulp.src(["./modules/wedding/views/src/shop/cart.html"])
        .pipe(revReplace({
            manifest: manifest
        }))
        .pipe(htmlmin(options))
        .pipe(gulp.dest('./modules/wedding/views/dist'));
})
