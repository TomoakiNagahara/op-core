
 ## op-core:/ci.sh
 #
 # Call from git pre-push
 #
 # @created   2022-10-31
 # @updated   2023-02-09 v2.0
 # @version   2.0
 # @package   op-core
 # @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 # @copyright Tomoaki Nagahara All right reserved.

# Get command
COMMAND=$(ps -ocommand= -p $PPID)

# Parse
ARRAY=(${COMMAND//,/ })
REMOTE=${ARRAY[2]}
BRANCH=${ARRAY[3]}

# Get current branch name
#BRANCH=`git rev-parse --abbrev-ref HEAD`
if [ ! $BRANCH ]; then
  echo "Empty branch name."
  exit 1
fi

# Set CI saved commit id file name
CI_FILE=".ci_commit_id_"$BRANCH
#echo $CI_FILE

# Check if file exists
if [ ! -f $CI_FILE ]; then
  echo "Does not file exists. ($CI_FILE)"
  exit 1
fi

# Get commit id
CI_COMMIT_ID=`cat $CI_FILE`
#echo $CI_COMMIT_ID

# Get correct commit id
COMMIT_ID=`git rev-parse $BRANCH`
#echo $COMMIT_ID

#
if [ $COMMIT_ID != $CI_COMMIT_ID ]; then
  echo "Unmatch commit id"
  echo $COMMIT_ID branch=$BRANCH
  echo $CI_COMMIT_ID $CI_FILE
  exit 1
fi
