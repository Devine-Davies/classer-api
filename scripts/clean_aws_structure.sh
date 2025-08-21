#!/bin/bash

# === Define naming components ===
PROJECT="classer"
ENV="pilot"
REGIONS=("eu-west-2")
FOLDERS=("cloud-share/")

# === Loop through regions and delete buckets ===
for REGION in "${REGIONS[@]}"; do
  BUCKET_NAME="${PROJECT}-${ENV}-${REGION}"
  echo "Deleting all objects from: $BUCKET_NAME in $REGION..."

  # Empty the bucket (required before deletion)
  aws s3 rm s3://$BUCKET_NAME --recursive || echo "⚠️  Failed to empty $BUCKET_NAME"

  echo "Deleting bucket: $BUCKET_NAME in $REGION..."
  aws s3api delete-bucket \
    --bucket "$BUCKET_NAME" \
    --region "$REGION" \
    || echo "⚠️  Failed to delete $BUCKET_NAME"

done
