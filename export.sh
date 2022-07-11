#!/bin/bash
mkdir bienoubien
rsync -av --progress . ./bienoubien --exclude .git
zip -r bienoubien.zip bienoubien
rm -r bienoubien
