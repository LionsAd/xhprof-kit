## About

Note: Using this on a production system is unsupported and potentially dangerous.

## Setup

0. Install XHProf PHP Extension
   * This guide assumes you have the PHP XHProf extension installed already.
1. Download xhprof as submodule while in xhprof-kit dir:
   * $ git submodule init
   * $ git submodule update
2. Install XHProf-Kit in the Drupal directory you want to benchmark
   * $ /where/is/xhprof-kit/setup-directory.sh
   * This creates a xhprof-kit symlink in the directory.
   * Make sure symlinks are allowed in Apache configuration.
3. Install drush registry rebuild project in drush folder. (optional)
   * $ drush dl registry_rebuild

## Assumptions

Currently XHProf-Kit assumes that Drupal is installed at 127.0.0.1. If you use
a virtual host, you will need to change the URL in find-min-web.sh.

## Benchmarking

#### Create a scenario

* Install Drupal and setup a page you want to benchmark.
* Use devel and devel_generate to create a suitable page.
* For example: 50 nodes on frontpage

Now lets assume the patch you want to benchmark is in:

* core--issueno

and the vanilla core is in 8.x branch.

#### Find a base line

First you will need to find a base line, which is what you compare things to.

Use:

````
./xhprof-kit/benchmark-branch.sh 8.x
````

The output will be something like:

````
loop time: |0.345083s|5173f49dd982a|drupal-perf|8.x|<a href="http://127.0.0.1/xhprof-kit/xhprof/xhprof_html/index.php?run=5173f49dd982a&source=drupal-perf&extra=8.x&url=%2F" target="_blank">Profiler output</a>
````

The fields delimited by '|' are:

1. "loop time" - Just a string
2. loop time - The actual time Drupal needed to load the page.
3. xhprof-identifier - An identifier for the run that can be used to see the run via xhprof_html/index.php utility.
4. xhprof-source - A suffix used to distinguish runs - currently set to "drupal-perf".
5. extra identifier - An identifier used internally by xhprof-kit; usually the branch name.
6. Run URL - The url, where the XHProf output can be seen.

What you need as your baseline is the xhprof identifier: '5173f49dd982a'.

Note that as scenarios and core changes, so will your baseline, so be sure to
always re-calculate it when starting a different benchmarking scenario.

#### Compare Branches

To now benchmark your baseline against 8.x again (to verify it is accurate)
and against your new branch with the patch to test, use the following command:

````
./xhprof-kit/benchmark-branches.sh <your baseline identifier> baseline-8.x 8.x core--issueno
````

The output of this will be something like:

````
=== baseline-8.x..8.x compared (5173f49dd982a..5172adc3e447f):

ct  : 47,077|47,077|0|0.0%
wt  : 396,923|397,915|992|0.2%
cpu : 370,881|370,295|-586|-0.2%
mu  : 30,347,736|30,349,184|1,448|0.0%
pmu : 30,472,168|30,475,144|2,976|0.0%

=== baseline-8.x..core--issueno compared (5172ad6a9a6b6..5172ae167ba6d):

ct  : 47,077|47,207|130|0.3%
wt  : 396,923|399,899|2,976|0.7%
cpu : 370,881|373,186|2,305|0.6%
mu  : 30,347,736|30,440,776|93,040|0.3%
pmu : 30,472,168|30,564,656|92,488|0.3%

---
ct = function calls, wt = wall time, cpu = cpu time used, mu = memory usage, pmu = peak memory usage
````

### Hints

* Update 100 to 1000 in benchmark-branch.sh to have more accurate runs.
  * 1000 takes a while, but is very accurate. It depends on the speed of your machine and how dedicated it can be, if 100 runs are enough to find the minimum.
* 'baseline-8.x' is just a name, its a good idea to point out what the benchmark is here, like 'core-stark-10-nodes'.
