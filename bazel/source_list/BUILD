load("@bazel_tools//tools/build_defs/pkg:pkg.bzl", "pkg_tar", "pkg_deb")

filegroup(
    name = "init",
    srcs = [
        "@ddtrace_bridge//:dd_init.php"
    ]
)

filegroup(
    name = "autoloaders",
    srcs = [
        "@ddtrace_bridge//:dd_optional_deps_autoloader.php",
        "@ddtrace_bridge//:dd_required_deps_autoloader.php",
    ]
)

genrule(
    name = "source_list",
    srcs = [":init", ":autoloaders"],
    outs = ["source_list.txt"],
    cmd = "PHPRC=$(location php.ini) php $(location dd_compiler.php) $(locations :autoloaders) $(location :init) > \"$@\"",
    tools = ["dd_compiler.php", "php.ini"],
    visibility = ["//visibility:public"],
)
