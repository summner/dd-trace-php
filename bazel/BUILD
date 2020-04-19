load("@bazel_tools//tools/build_defs/pkg:pkg.bzl", "pkg_tar", "pkg_deb")

pkg_tar(
    name = "hmm",
    strip_prefix = "../ddtrace_bridge",
    package_dir = "/bridge",
    srcs = ["@ddtrace_bridge"],
)

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
    name = "foo",
    srcs = [":init", ":autoloaders"],
    outs = ["list.out"],
    cmd = "php $(location dd_compiler.php) $(locations :autoloaders) $(location :init) > \"$@\"",
    tools = ["dd_compiler.php"],
)
