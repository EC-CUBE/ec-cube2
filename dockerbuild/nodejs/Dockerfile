FROM node:lts

ARG ARCH=linux64
RUN apt update \
    && apt install -y unzip curl apt-transport-https gnupg \
    && curl -fsSL https://dl.google.com/linux/linux_signing_key.pub | apt-key add - \
    && curl -O https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb \
    && apt install -y ./google-chrome-stable_current_amd64.deb \
    && apt clean \
    && rm -rf /var/lib/apt/lists/* \
    && rm ./google-chrome-stable_current_amd64.deb

RUN CHROME_VERSION=$(google-chrome --version | cut -f 3 -d ' ' | cut -d '.' -f 1) \
    && VERSION=$(curl --location --fail --retry 10 http://chromedriver.storage.googleapis.com/LATEST_RELEASE_${CHROME_VERSION}) \
    && curl -O https://chromedriver.storage.googleapis.com/${VERSION}/chromedriver_${ARCH}.zip \
    && unzip -o -q chromedriver_${ARCH}.zip \
    && mv chromedriver /usr/local/bin/chromedriver \
    && rm chromedriver_${ARCH}.zip
