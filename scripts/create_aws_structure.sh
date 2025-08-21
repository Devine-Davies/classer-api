#!/bin/bash

# === Define naming components ===
PROJECT="classer"
ENV="pilot"
REGIONS=("eu-west-2")
FOLDERS=("cloud-share/")

# === Loop through regions and create buckets ===
for REGION in "${REGIONS[@]}"; do
  BUCKET_NAME="${PROJECT}-${ENV}-${REGION}"
  echo "Creating bucket: $BUCKET_NAME in $REGION..."

  if [ "$REGION" == "us-east-1" ]; then
    aws s3api create-bucket \
      --bucket "$BUCKET_NAME" \
      --region "$REGION"
  else
    aws s3api create-bucket \
      --bucket "$BUCKET_NAME" \
      --region "$REGION" \
      --create-bucket-configuration LocationConstraint="$REGION"
  fi

  # Create folder structure
  echo "Creating folder structure in $BUCKET_NAME..."
  for FOLDER in "${FOLDERS[@]}"; do
    aws s3api put-object \
      --bucket "$BUCKET_NAME" \
      --key "$FOLDER"
  done

done