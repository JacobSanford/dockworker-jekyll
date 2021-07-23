FROM jekyll/jekyll:3.8 as jekyll
RUN chown jekyll:jekyll /
WORKDIR /build/src

RUN id jekyll

CMD ["jekyll", "build", "--destination", "/dist", "--watch"]
