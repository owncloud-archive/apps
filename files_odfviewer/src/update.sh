cd webodf
git pull
cd ..
rm -Rf build
mkdir build
cd build
cmake ../webodf
make webodf.js
 cp webodf/webodf.js ../../js/
