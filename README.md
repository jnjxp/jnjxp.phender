# phender

Quick and dirty cli php template rendering.

## Usage

See example directory...

```sh
$ ./bin/phender \
    -i articles:./example/article-data.php \
    -v "site:My Site" \
    -h ./example/helpers.php \
    ./example/template/articles.phtml \
    ./example/template/layout.phtml > articles.html
```

- `-i/--include VAR:PATH` sets `VAR` to value returned by including `PATH`
- `-v/--var VAR:VALUE` sets `VAR` to `VALUE`
- `-h/--helpers PATH` sets helpers to value returned by including `PATH`. Should
  return an array of callables.
