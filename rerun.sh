#!/bin/bash
imageName=dvwa
containerName=my-dvwa-container

docker build -t $imageName -f Dockerfile  .

echo Delete old container...
docker rm -f $containerName

echo Run new container...
docker run -d -p 8155:80 --name $containerName $imageName

echo Clean old images...
yes Y | docker image prune --filter="label=maintainer=opsxcq@strm.sh"
