#/bin/bash
# 
# Cloning units and layouts.
# 
# @creation  2016-02-23
# @version   1.0
# @package   op-core
# @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
# @copyright Tomoaki Nagahara All right reserved.

# Make directory.
mkdir unit layout

# Ready unit array.
UNITS=("selftest" "language" "google" "social" "markdown" "wikipedia")

# Change directory.
cd unit

# Cloning.
for UNIT in "${UNITS[@]}";
do
	CMD="git clone https://github.com/TomoakiNagahara/op-unit-$UNIT.git $UNIT"
	echo $CMD
	$CMD
done

# Ready layout array.
LAYOUTS=("white" "flat" "i18n")

# Change directory.
cd ../layout

# Cloning.
for LAYOUT in "${LAYOUTS[@]}";
do
	CMD="git clone https://github.com/TomoakiNagahara/op-layout-$LAYOUT.git $LAYOUT"
	echo $CMD
	$CMD
done

# Finished.
echo 'FINISHED'
