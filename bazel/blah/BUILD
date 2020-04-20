genrule(
    name = "blah",
    srcs = ["//source_list"],
    outs = ["blah.h"],
    cmd = "perl $(location @io_github_com_julian_klode_triehash) $(location //source_list) > \"$@\"",
    tools = ["@io_github_com_julian_klode_triehash"],
    visibility = ["//visibility:public"]
)
