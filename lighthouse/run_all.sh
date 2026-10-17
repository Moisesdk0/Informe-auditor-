#!/bin/bash
BASE="http://localhost/buildsafe_system1"
CATS="performance,accessibility,best-practices,seo"
run() { # name path mode auth
  name=$1; path=$2; mode=$3; auth=$4
  out="./out/${name}_${mode}"
  [ -f "${out}.report.json" ] && return
  args=(--only-categories=$CATS --chrome-flags="--headless=new" --output=json --output=html --output-path="$out" --quiet)
  [ "$mode" = "desktop" ] && args+=(--preset=desktop)
  [ "$auth" = "yes" ] && args+=(--extra-headers=./headers.json)
  echo "== $name $mode"
  npx lighthouse "$BASE/$path" "${args[@]}" 2>&1 | tail -3
}
for mode in desktop mobile; do
  run login "" $mode no
  run dashboard dashboard.php $mode yes
  run list_materials list_materials.php $mode yes
  run list_materials2 list_materials2.php $mode yes
  run list_material list_material.php $mode yes
  run list_user list_user.php $mode yes
  run binnacle binnacle.php $mode yes
  run file file.php $mode yes
done
echo ALL_DONE
