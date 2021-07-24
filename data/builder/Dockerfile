FROM jekyll/jekyll:3.8 as jekyll
RUN chown jekyll:jekyll /
WORKDIR /build/src
COPY build/scripts /scripts

CMD ["/scripts/run.sh"]
